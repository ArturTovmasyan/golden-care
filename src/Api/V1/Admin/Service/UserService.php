<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use App\Entity\UserPhone;
use Doctrine\ORM\QueryBuilder;

/**
 * Class UserService
 * @package App\Api\V1\Service
 */
class UserService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(User::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(User::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return null|object|User
     */
    public function getById($id)
    {
        $user = $this->em->getRepository(User::class)->getOne($this->grantService->getCurrentSpace(), $id);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->em->getRepository(User::class)->find($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            // create user
            $user = new User();
            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setUsername(strtolower($params['username']));
            $user->setEmail($params['email']);
            $user->setEnabled((bool) $params['enabled']);
            $user->setGrants($params['grants']);
            $user->setSpace($space);

            if(\count($params['roles']) > 0) {
                $roles = [];
                foreach ($params['roles'] as $role_id) {
                    /** @var Role $role */
                    $role = $this->em->getRepository(Role::class)->find($role_id);
                    if($role) {
                        $roles[] = $role;
                    }
                }
                $user->setRoles($roles);
            }

            $user->setLastActivityAt(new \DateTime());

            $user->setCompleted(true);

            $user->setPlainPassword($params['password']);
            $user->setConfirmPassword($params['re_password']);
            $user->setPhones($this->savePhones($user, $params['phones'] ?? []));

            $this->validate($user, null, ['api_admin_user_add']);

            $encoded = $this->encoder->encodePassword($user, $params['password']);
            $user->setPassword($encoded);

            $this->em->persist($user);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var User $user */
            $user = $this->em->getRepository(User::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($user === null) {
                throw new UserNotFoundException();
            }

            /** @var Space $space */
            $space = $this->em->getRepository(User::class)->find($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setUsername(strtolower($params['username']));
            $user->setEmail($params['email']);
            $user->setEnabled((bool) $params['enabled']);
            $user->setGrants($params['grants']);
            $user->setSpace($space);

            if(\count($params['roles']) > 0) {
                $roles = [];
                foreach ($params['roles'] as $role_id) {
                    /** @var Role $role */
                    $role = $this->em->getRepository(Role::class)->find($role_id);
                    if($role) {
                        $roles[] = $role;
                    }
                }
                $user->setRoles($roles);
            }

            if(!empty($params['password'])) {
                $user->setPlainPassword($params['password']);
                $user->setConfirmPassword($params['re_password']);
            }

            $user->setPhones($this->savePhones($user, $params['phones'] ?? []));

            $this->validate($user, null, ['api_admin_user_edit']);

            if(!empty($params['password'])) {
                $encoded = $this->encoder->encodePassword($user, $params['password']);
                $user->setPassword($encoded);
            }

            $this->em->persist($user);
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
    public function resetPassword($id)
    {

        try {
            $this->em->getConnection()->beginTransaction();

            /** @var User $user **/
            $user = $this->em->getRepository(User::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($user === null) {
                return;
            }

            $this->em->getConnection()->beginTransaction();

            $password = $this->generatePassword(8);
            $encoded  = $this->encoder->encodePassword($user, $password);

            $user->setPlainPassword($password);
            $user->setPassword($encoded);
            $this->em->persist($user);

            $this->validate($user, null, ['api_admin_user_reset_password']);

            $this->em->flush();

            // send email for new credentials
            $this->mailer->notifyCredentials($user);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param User $user
     * @param array $phones
     * @return array
     */
    private function savePhones($user, array $phones = [])
    {
        /**
         * @var UserPhone[] $oldPhones
         */
        $oldPhones = $this->em->getRepository(UserPhone::class)->getBy($this->grantService->getCurrentSpace(), $user);

        foreach ($oldPhones as $phone) {
            $this->em->remove($phone);
        }

        $hasPrimary = false;

        $userPhones = [];

        foreach($phones as $phone) {
            $primary = $phone['primary'] ? (bool) $phone['primary'] : false;
            $smsEnabled = $phone['sms_enabled'] ? (bool) $phone['sms_enabled'] : false;

            $userPhone = new UserPhone();
            $userPhone->setUser($user);
            $userPhone->setCompatibility($phone['compatibility'] ?? null);
            $userPhone->setType($phone['type']);
            $userPhone->setNumber($phone['number']);
            $userPhone->setPrimary($primary);
            $userPhone->setSmsEnabled($smsEnabled);

            if ($userPhone->isPrimary()) {
                if ($hasPrimary) {
                    throw new PhoneSinglePrimaryException();
                }

                $hasPrimary = true;
            }

            $this->em->persist($userPhone);

            $userPhones[] = $userPhone;
        }

        return $userPhones;
    }
}
