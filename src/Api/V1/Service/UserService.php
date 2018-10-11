<?php
namespace App\Api\V1\Service;


use App\Api\V1\Service\Exception\DuplicateUserException;
use App\Api\V1\Service\Exception\IncorrectRepeatPasswordException;
use App\Api\V1\Service\Exception\SystemErrorException;
use App\Api\V1\Service\Exception\UserNotFoundException;
use App\Api\V1\Service\Exception\ValidationException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUserRole;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;
use App\Util\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
     * UserService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Mailer $mailer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        Mailer $mailer,
        ValidatorInterface $validator
    ) {
        $this->em        = $em;
        $this->encoder   = $encoder;
        $this->mailer    = $mailer;
        $this->validator = $validator;
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
            throw new UserNotFoundException(
                sprintf("User by id %d not found", $id),
                Response::HTTP_BAD_REQUEST
            );
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

        if ($params['password'] !== $params['rePassword']) {
            throw new IncorrectRepeatPasswordException();
        }

        /** @var Role $defaultRoleForSpace **/
        $defaultRoleForSpace = $this->em->getRepository(Role::class)->getSpaceDefaultRole();

        try {
            $this->em->getConnection()->beginTransaction();

            // create user
            $user = new User();
            $user->setFirstName($params['firstName']);
            $user->setLastName($params['lastName']);
            $user->setUsername(strtolower($params['firstName']) . time());
            $user->setEmail($params['email']);
            $user->setLastActivityAt(new \DateTime());
            $user->setEnabled(false);

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
            $this->em->persist($space);

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

        if ($params['newPassword'] !== $params['confirmPassword']) {
            throw new ValidationException([
                'password' => 'New password is not confirmed'
            ]);
        }

        if ($params['newPassword'] == $params['password']) {
            throw new ValidationException([
                'password' => 'New password must be different from last password'
            ]);
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $encoded = $this->encoder->encodePassword($user, $params['newPassword']);
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

        if ($params['newPassword'] !== $params['confirmPassword']) {
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
}