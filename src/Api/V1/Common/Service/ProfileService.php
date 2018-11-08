<?php
namespace App\Api\V1\Common\Service;

use App\Entity\User;

/**
 * Class ProfileService
 * @package App\Api\V1\Service
 */
class ProfileService extends BaseService
{
    /**
     * @param User $user
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit(User $user, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $user->setOldPassword($params['password']);
            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setEmail($params['email']);
            $user->setPhone($params['phone']);

            $this->validate($user, null, ["api_profile_edit"]);

            $this->em->persist($user);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
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
        try {
            $this->em->getConnection()->beginTransaction();

            $encoded = $this->encoder->encodePassword($user, $params['new_password']);

            $user->setOldPassword($params['password']);
            $user->setPlainPassword($params['new_password']);
            $user->setConfirmPassword($params['re_new_password']);
            $user->setPassword($encoded);

            $this->validate($user, null, ["api_profile_change_password"]);

            $this->em->persist($user);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}