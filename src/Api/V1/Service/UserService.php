<?php
namespace App\Api\V1\Service;


use App\Api\V1\Service\Exception\DuplicateUserException;
use App\Api\V1\Service\Exception\IncorrectRepeatPasswordException;
use App\Api\V1\Service\Exception\InvalidConfirmationTokenException;
use App\Api\V1\Service\Exception\RoleNotFoundException;
use App\Api\V1\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Service\Exception\SpaceUserNotFoundException;
use App\Api\V1\Service\Exception\SystemErrorException;
use App\Api\V1\Service\Exception\UserAlreadyJoinedException;
use App\Api\V1\Service\Exception\UserNotFoundException;
use App\Api\V1\Service\Exception\ValidationException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUser;
use App\Entity\SpaceUserRole;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;
use App\Util\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserService
 * @package App\Api\V1\Service
 */
class UserService
{
    use ControllerTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Security
     */
    protected $security;

    /**
     * UserService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     * @param Security $security
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        Mailer $mailer,
        ValidatorInterface $validator,
        Security $security
    ) {
        $this->em        = $em;
        $this->encoder   = $encoder;
        $this->mailer    = $mailer;
        $this->validator = $validator;
        $this->security  = $security;
    }

    /**
     * @param int $length
     * @return bool|string
     */
    private function generatePassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";

        return substr(str_shuffle( $chars ), 0, $length);
    }

    /**
     * Reset User password
     *
     * @param $id
     * @return void
     * @throws UserNotFoundException|\Doctrine\DBAL\ConnectionException
     */
    public function resetPassword($id)
    {
        /** @var User $user **/
        $user = $this->em->getRepository(User::class)->find($id);

        if (is_null($user)) {
            return;
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $password = $this->generatePassword(8);
            $encoded  = $this->encoder->encodePassword($user, $password);

            $user->setPlainPassword($password);
            $user->setPassword($encoded);
            $this->em->persist($user);
            $this->em->flush();

            // send email for new credentials
            $this->mailer->notifyCredentials($user);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * Register User
     *
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function signup(array $params)
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $params['email']]);

        if ($user) {
            throw new DuplicateUserException();
        }

        if ($params['password'] !== $params['re_password']) {
            throw new IncorrectRepeatPasswordException();
        }

        /** @var Role $defaultRoleForSpace **/
        $defaultRoleForSpace = $this->em->getRepository(Role::class)->getSpaceDefaultRole();

        try {
            $this->em->getConnection()->beginTransaction();

            // create user
            $user = new User();
            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setUsername(strtolower($params['last_name']) . time());
            $user->setEmail($params['email']);
            $user->setLastActivityAt(new \DateTime());
            $user->setEnabled(true);
            $user->setCompleted(true);

            // encode password
            $encoded = $this->encoder->encodePassword($user, $params['password']);
            $user->setPassword($encoded);

            // validate user
            $validationErrors = $this->validator->validate($user, null, ["api_user__signup"]);
            $errors           = [];

            if ($validationErrors->count() > 0) {
                foreach ($validationErrors as $error) {
                    $errors[$error->getPropertyPath()] = $error->getMessage();
                }

                throw new ValidationException($errors);
            }

            $this->em->persist($user);

            // create space
            $space = new Space();
            $space->setOwner($user);
            $this->em->persist($space);

            // connect user to space
            $spaceUser = new SpaceUser();
            $spaceUser->setSpace($space);
            $spaceUser->setUser($user);
            $spaceUser->setStatus(\App\Model\SpaceUser::STATUS_ACCEPTED);

            // create space user roles
            if ($defaultRoleForSpace) {
                $spaceUserRole = new SpaceUserRole();
                $spaceUserRole->setUser($user);
                $spaceUserRole->setRole($defaultRoleForSpace);
                $spaceUserRole->setSpace($space);
                $this->em->persist($spaceUserRole);
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setType(UserLog::LOG_TYPE_AUTHENTICATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf("User %s (%s) registered in ", $user->getFullName(), $user->getUsername()));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * Change User Password
     *
     * @param User $user
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function changePassword(User $user, array $params)
    {
        if (!$this->encoder->isPasswordValid($user, $params['password'])) {
            throw new ValidationException([
                'password' => 'Invalid current password'
            ]);
        }

        if ($params['new_password'] !== $params['re_new_password']) {
            throw new ValidationException([
                'password' => 'New password is not confirmed'
            ]);
        }

        if ($params['new_password'] == $params['password']) {
            throw new ValidationException([
                'password' => 'New password must be different from last password'
            ]);
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $encoded = $this->encoder->encodePassword($user, $params['new_password']);
            $user->setPassword($encoded);

            $this->em->persist($user);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * @param string $email
     * @param string $baseUrl
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function forgotPassword(string $email, string $baseUrl)
    {
        /** @var User $user **/
        $user = $this->em->getRepository(User::class)->findOneByEmail($email);

        if (is_null($user)) {
            throw new UserNotFoundException(
                sprintf("User by email %s not found", $email),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $user->setPasswordRecoveryHash();
            $this->em->persist($user);
            $this->em->flush();

            // send mail with complete url
            $this->mailer->sendPasswordRecoveryLink($user, $baseUrl);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function confirmPassword(array $params)
    {
        /** @var User $user **/
        $user = $this->em->getRepository(User::class)->findOneBy(['passwordRecoveryHash' => $params['hash']]);

        if (is_null($user)) {
            throw new UserNotFoundException(
                sprintf("User by hash %s not found", $params['hash']),
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($params['new_password'] !== $params['re_password']) {
            throw new ValidationException([
                'password' => 'New password is not confirmed.'
            ]);
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $user->setPasswordRecoveryHash();
            $this->em->persist($user);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * @param $spaceId
     * @param $email
     * @param $roleId
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function invite($spaceId, $email, $roleId)
    {
        /**
         * @var User $user|null
         * @var Space $space|null
         * @var Role $role|null
         */
        $user  = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        $space = $this->em->getRepository(Space::class)->find($spaceId);
        $role  = $this->em->getRepository(Role::class)->find($roleId);

        if (is_null($space)) {
            throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
        }

        if (is_null($role)) {
            throw new RoleNotFoundException(Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->em->getConnection()->beginTransaction();

            // create user if not exist
            if (is_null($user)) {
                $user = new User();
                $user->setEmail($email);
                $user->setLastActivityAt(new \DateTime());
                $user->setEnabled(false);
                $user->setCompleted(false);

                // validate user
                $validationErrors = $this->validator->validate($user, null, ["api_user__invite"]);
                $errors           = [];

                if ($validationErrors->count() > 0) {
                    foreach ($validationErrors as $error) {
                        $errors[$error->getPropertyPath()] = $error->getMessage();
                    }

                    throw new ValidationException($errors);
                }

                $this->em->persist($user);
            }

            /** @var SpaceUser $spaceUser|null **/
            $spaceUser = $this->em
                ->getRepository(SpaceUser::class)
                ->findOneBy(
                    [
                        'space' => $space,
                        'user'  => $user,
                    ]
                );

            if (!is_null($spaceUser) && $spaceUser->isAccepted()) {
                throw new UserAlreadyJoinedException();
            }

            // create space user relation if not exist
            if (is_null($spaceUser)) {
                $spaceUser = new SpaceUser();
                $spaceUser->setUser($user);
                $spaceUser->setSpace($space);
                $spaceUser->setStatus(\App\Model\SpaceUserRole::STATUS_INVITED);

                if (!$user->isCompleted()) {
                    $spaceUser->generateConfirmationToken();
                }

                $this->em->persist($spaceUser);

                // create space user default roles
                /** @var Role $defaultRoleForSpace **/
                $defaultRoleForSpace = $this->em->getRepository(Role::class)->getSpaceDefaultRole();

                if ($defaultRoleForSpace) {
                    $spaceUserRole = new SpaceUserRole();
                    $spaceUserRole->setUser($user);
                    $spaceUserRole->setRole($defaultRoleForSpace);
                    $spaceUserRole->setSpace($space);
                    $this->em->persist($spaceUserRole);
                }
            }

            // send email to customer
            if (!$spaceUser->isAccepted()) {
                $joinUrl = false;

                if (!$user->isCompleted()) {
                    /** @todo change to real url from frontend **/
                    $joinUrl = 'http://localhost:4200';
                }

                $this->mailer->inviteUser($user, $joinUrl);
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setSpace($space);
            $log->setType(UserLog::LOG_TYPE_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf("User %s (%s) invited to join space ", $user->getFullName(), $user->getUsername()));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * @param $spaceId
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function acceptInvitation($spaceId)
    {
        /**
         * @var User $user
         * @var Space $space|null
         * @var Role $role|null
         */
        $user  = $this->security->getToken()->getUser();
        $space = $this->em->getRepository(Space::class)->find($spaceId);

        if (is_null($space)) {
            throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
        }

        if (!$user->isCompleted()) {
            throw new UserNotFoundException('User haven\'t completed account');
        }

        try {
            $this->em->getConnection()->beginTransaction();

            /** @var SpaceUser $spaceUser|null **/
            $spaceUser = $this->em
                ->getRepository(SpaceUser::class)
                ->findOneBy(
                    [
                        'space' => $space,
                        'user'  => $user,
                    ]
                );

            if (is_null($spaceUser)) {
                throw new SpaceUserNotFoundException();
            }

            if ($spaceUser->isAccepted()) {
                throw new UserAlreadyJoinedException();
            }

            if (!empty($spaceUser->getConfirmationToken())) {
                throw new UserNotFoundException('User haven\'t completed account, please check email for confirmation account.');
            }

            $spaceUser->setStatus(\App\Model\SpaceUserRole::STATUS_ACCEPTED);
            $this->em->persist($spaceUser);

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setSpace($space);
            $log->setType(UserLog::LOG_TYPE_ACCEPT_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf("User %s (%s) accept invitation for space", $user->getFullName(), $user->getUsername()));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ValidationException|\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * @param $spaceId
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rejectInvitation($spaceId)
    {
        /**
         * @var User $user
         * @var Space $space|null
         * @var Role $role|null
         */
        $user  = $this->security->getToken()->getUser();
        $space = $this->em->getRepository(Space::class)->find($spaceId);

        if (is_null($space)) {
            throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
        }

        if (!$user->isCompleted()) {
            throw new UserNotFoundException('User haven\'t completed account');
        }

        try {
            $this->em->getConnection()->beginTransaction();

            /** @var SpaceUser $spaceUser|null **/
            $spaceUser = $this->em
                ->getRepository(SpaceUser::class)
                ->findOneBy(
                    [
                        'space' => $space,
                        'user'  => $user,
                    ]
                );

            if (is_null($spaceUser)) {
                throw new SpaceUserNotFoundException();
            }

            if ($spaceUser->isAccepted()) {
                throw new UserAlreadyJoinedException();
            }

            $this->em->remove($spaceUser);

            // remove all roles
            $spaceUserRoles = $this->em
                ->getRepository(SpaceUserRole::class)
                ->findBy(
                    [
                        'space' => $space,
                        'user'  => $user,
                    ]
                );

            foreach ($spaceUserRoles as $spaceUserRole) {
                $this->em->remove($spaceUserRole);
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setSpace($space);
            $log->setType(UserLog::LOG_TYPE_REJECT_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf("User %s (%s) reject invitation for space", $user->getFullName(), $user->getUsername()));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ValidationException|\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }

    /**
     * @param $spaceId
     * @param $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function completeInvitation($spaceId, $params)
    {
        /**
         * @var User $user
         * @var Space $space|null
         */
        $user  = $this->em->getRepository(User::class)->findOneBy(['email' => $params['email']]);
        $space = $this->em->getRepository(Space::class)->find($spaceId);

        if (is_null($user)) {
            throw new UserNotFoundException(sprintf('User with email %s not found', $params['email']), Response::HTTP_BAD_REQUEST);
        }

        if (is_null($space)) {
            throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->em->getConnection()->beginTransaction();

            /** @var SpaceUser $spaceUser|null **/
            $spaceUser = $this->em
                ->getRepository(SpaceUser::class)
                ->findOneBy(
                    [
                        'space' => $space,
                        'user'  => $user,
                    ]
                );

            if (is_null($spaceUser)) {
                throw new SpaceUserNotFoundException();
            }

            if ($spaceUser->isAccepted()) {
                throw new UserAlreadyJoinedException();
            }

            if (empty($params['token']) || $params['token'] != $spaceUser->getConfirmationToken()) {
                throw new InvalidConfirmationTokenException();
            }

            // update spaceUser
            $spaceUser->cleanConfirmationToken();
            $spaceUser->setStatus(\App\Model\SpaceUserRole::STATUS_ACCEPTED);
            $this->em->persist($spaceUser);

            // update user if not completed
            if (!$user->isCompleted()) {
                $user->setFirstName($params['first_name']);
                $user->setLastName($params['last_name']);
                $user->setUsername(strtolower($params['first_name']) . time());
                $user->setLastActivityAt(new \DateTime());
                $user->setEnabled(true);
                $user->setCompleted(true);

                // encode password
                $encoded = $this->encoder->encodePassword($user, $params['password']);
                $user->setPassword($encoded);

                // validate user
                $validationErrors = $this->validator->validate($user, null, ["api_user__complete"]);
                $errors           = [];

                if ($validationErrors->count() > 0) {
                    foreach ($validationErrors as $error) {
                        $errors[$error->getPropertyPath()] = $error->getMessage();
                    }

                    throw new ValidationException($errors);
                }

                $this->em->persist($user);
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setSpace($space);
            $log->setType(UserLog::LOG_TYPE_ACCEPT_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf("User %s (%s) accept invitation for space", $user->getFullName(), $user->getUsername()));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ValidationException|\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw new SystemErrorException();
        }
    }
}