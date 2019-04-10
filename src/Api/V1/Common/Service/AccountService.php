<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\Exception\DefaultRoleNotFoundException;
use App\Api\V1\Common\Service\Exception\InvalidRecoveryLinkException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SystemErrorException;
use App\Api\V1\Common\Service\Exception\UserAlreadyInvitedException;
use App\Api\V1\Common\Service\Exception\UserAlreadyJoinedException;
use App\Api\V1\Common\Service\Exception\UserNotYetInvitedException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\UserInvite;
use App\Entity\UserLog;
use App\Entity\UserPhone;
use App\Model\Log;
use App\Model\Phone;
use App\Repository\RoleRepository;

/**
 * Class AccountService
 * @package App\Api\V1\Service
 */
class AccountService extends BaseService
{
    /**
     * Register User
     *
     * @param array $params
     * @param string $baseUrl
     * @throws \Exception
     */
    public function signup(array $params, string $baseUrl)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var RoleRepository $roleRepo */
            $roleRepo = $this->em->getRepository(Role::class);

            /** @var Role $defaultRole **/
            $defaultRole = $roleRepo->getDefaultRole();

            if ($defaultRole === null) {
                throw new DefaultRoleNotFoundException();
            }

            // create space
            $space = new Space();
            $space->setName($params['organization']);
            $this->validate($space, null, ['api_account_signup']);
            $this->em->persist($space);

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
            $user->setOwner(true);
            $user->setSpace($space);
            $user->setPhone($params['phone']);

            // validate user
            $this->validate($user, null, ['api_account_signup']);

            if ($defaultRole) {
                $user->getRoleObjects()->add($defaultRole);
            }

            $this->em->persist($user);

            if($params['phone']) { // TODO: review
                $userPhone = new UserPhone();
                $userPhone->setUser($user);
                $userPhone->setCompatibility( null);
                $userPhone->setType(Phone::TYPE_OFFICE);
                $userPhone->setNumber($user->getPhone());
                $userPhone->setPrimary(true);
                $userPhone->setSmsEnabled(false);

                $this->em->persist($userPhone);
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setType(UserLog::LOG_TYPE_AUTHENTICATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('User %s (%s) registered in ', $user->getFullName(), $user->getUsername()));
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

        if ($user === null) {
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
     * @throws \Exception
     */
    public function forgotPassword(string $email, string $baseUrl)
    {
        /** @var User $user **/
        $user = $this->em->getRepository(User::class)->findOneByEmail($email);

        if ($user === null) {
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

        if ($user === null) {
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

            $this->validate($user, null, ['api_account_reset_password']);

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
     * @param $roles
     * @throws \Exception
     */
    public function invite($spaceId, $email, $roles)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /**
             * @var UserInvite $userInvite|null
             * @var Space $space|null
             */
            $userInvite = $this->em->getRepository(UserInvite::class)->findOneBy(['email' => $email]);
            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($userInvite !== null) {
                throw new UserAlreadyInvitedException();
            }

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $userInvite = new UserInvite();
            $userInvite->setEmail($email);
            $userInvite->setToken();
            $userInvite->setSpace($space);

            if(\count($roles) > 0) {
                $userInvite->getRoleObjects()->clear();

                foreach ($roles as $roleId) {
                    /** @var Role $role */
                    $role = $this->em->getRepository(Role::class)->find($roleId);
                    if($role) {
                        $userInvite->getRoleObjects()->add($role);
                    }
                }
            }

            // validate user
            $this->validate($userInvite, null, ['api_admin_user_invite']);

            $this->em->persist($userInvite);

            /** @todo change to real url from frontend **/
            $joinUrl = 'http://localhost:4200';
            $this->mailer->inviteUser($email, $joinUrl);

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser(null);
            $log->setSpace($space);
            $log->setType(UserLog::LOG_TYPE_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('User %s invited to join space ', $email));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $token
     * @param array $params
     * @throws \Exception
     */
    public function acceptInvitation($token, array $params)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /**
             * @var UserInvite $userInvite
             * @var User $user
             */
            $userInvite = $this->em->getRepository(UserInvite::class)->findOneBy(['token' => $token]);

            if ($userInvite === null) {
                throw new UserNotYetInvitedException();
            }

            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $userInvite->getEmail()]);

            if ($user !== null) {
                throw new UserAlreadyJoinedException();
            }

            // create user
            $user = new User();
            $user->setSpace($userInvite->getSpace());
            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setUsername(strtolower($params['last_name']) . time());
            $user->setEmail($userInvite->getEmail());
            $user->setLastActivityAt(new \DateTime());
            $user->setEnabled(true);
            $user->setCompleted(true);

            // encode password
            $encoded = $this->encoder->encodePassword($user, $params['password']);
            $user->setPlainPassword($params['password']);
            $user->setConfirmPassword($params['re_password']);
            $user->setPassword($encoded);
            $user->setActivationHash();
            $user->setOwner(false);
            $user->setPhone($params['phone']);

            // validate user
            $this->validate($user, null, ['api_account_signup']);

            if(\count($userInvite->getRoleObjects()) > 0) {
                $user->getRoleObjects()->clear();

                foreach ($userInvite->getRoleObjects() as $role) {
                        $user->getRoleObjects()->add($role);
                }
            }

            $this->em->persist($user);

            if($params['phone']) { // TODO: review
                $userPhone = new UserPhone();
                $userPhone->setUser($user);
                $userPhone->setCompatibility( null);
                $userPhone->setType(Phone::TYPE_OFFICE);
                $userPhone->setNumber($user->getPhone());
                $userPhone->setPrimary(true);
                $userPhone->setSmsEnabled(false);

                $this->em->persist($userPhone);
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser($user);
            $log->setSpace($user->getSpace());
            $log->setType(UserLog::LOG_TYPE_ACCEPT_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('User %s (%s) accept invitation for space', $user->getFullName(), $user->getUsername()));
            $this->em->persist($log);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function rejectInvitation($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /**
             * @var UserInvite $userInvite
             */
            $userInvite = $this->em->getRepository(UserInvite::class)->find($id);

            if ($userInvite === null) {
                throw new UserNotYetInvitedException();
            }

            // create log
            $log = new UserLog();
            $log->setCreatedAt(new \DateTime());
            $log->setUser(null);
            $log->setSpace($userInvite->getSpace());
            $log->setType(UserLog::LOG_TYPE_REJECT_INVITATION);
            $log->setLevel(Log::LOG_LEVEL_LOW);
            $log->setMessage(sprintf('User %s reject invitation for space', $userInvite->getEmail()));
            $this->em->persist($log);

            $this->em->remove($userInvite);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
