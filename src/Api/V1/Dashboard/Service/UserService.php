<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\InvalidConfirmationTokenException;
use App\Api\V1\Common\Service\Exception\InvalidRecoveryLinkException;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SystemErrorException;
use App\Api\V1\Common\Service\Exception\UserAlreadyJoinedException;
use App\Api\V1\Common\Service\Exception\SpaceUserNotFoundException;
use App\Api\V1\Common\Service\Exception\UserHaventConfirmationTokenException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\Exception\DuplicateUserException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUser;
use App\Entity\SpaceUserRole;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;

/**
 * Class UserService
 * @package App\Api\V1\Service
 */
class UserService extends BaseService
{
    /**
     * Register User
     *
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function signup(array $params, string $baseUrl)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Role $defaultRoleForSpace **/
            $defaultRoleForSpace = $this->em->getRepository(Role::class)->getSpaceDefaultRole();

            // create user
            $user = new User();
            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setUsername(strtolower($params['last_name']) . time());
            $user->setEmail($params['email']);
            $user->setLastActivityAt(new \DateTime());
            $user->setEnabled(false);
            $user->setCompleted(false);

            // encode password
            $encoded = $this->encoder->encodePassword($user, $params['password']);
            $user->setPlainPassword($params['password']);
            $user->setConfirmPassword($params['re_password']);
            $user->setPassword($encoded);
            $user->setActivationHash();

            // validate user
            $this->validate($user, null, ["api_dashboard_account_signup"]);
            $this->em->persist($user);

            // create space
            $space = new Space();
            $space->setName($params['organization']);
            $this->validate($space, null, ["api_dashboard_account_signup"]);
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

            // send mail with complete url
            $this->mailer->sendActivationLink($user, $baseUrl);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function activate(array $params)
    {
        /** @var User $user **/
        $user = $this->em->getRepository(User::class)->findOneBy(['activationHash' => $params['hash']]);

        if (is_null($user)) {
            throw new InvalidRecoveryLinkException();
        }

        try {
            $this->em->getConnection()->beginTransaction();

            $user->setActivationHash();
            $user->setEnabled(true);
            $user->setCompleted(true);

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
            return;
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

            throw $e;
        }
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function resetPassword(array $params)
    {
        /** @var User $user **/
        $user = $this->em->getRepository(User::class)->findOneBy(['passwordRecoveryHash' => $params['hash']]);

        if (is_null($user)) {
            throw new InvalidRecoveryLinkException();
        }

        try {
            $this->em->getConnection()->beginTransaction();

            // encode password
            $encoded = $this->encoder->encodePassword($user, $params['password']);

            $user->setConfirmPassword($params['re_password']);
            $user->setPlainPassword($params['password']);
            $user->setPassword($encoded);
            $user->setPasswordRecoveryHash();
            $this->em->persist($user);

            $this->validate($user, null, ["api_dashboard_account_reset_password"]);

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
            throw new SpaceNotFoundException();
        }

        if (is_null($role)) {
            throw new RoleNotFoundException();
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
                $this->validate($user, null, ["api_admin_user_invite"]);

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
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
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
            throw new SpaceNotFoundException();
        }

        if (!$user->isCompleted()) {
            throw new UserNotFoundException();
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
                throw new UserHaventConfirmationTokenException();
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
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
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
            throw new SpaceNotFoundException();
        }

        if (!$user->isCompleted()) {
            throw new UserNotFoundException();
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
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
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
            throw new UserNotFoundException();
        }

        if (is_null($space)) {
            throw new SpaceNotFoundException();
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
                $this->validate($user, null, ["api_dashboard_space_user_complete"]);

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
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
