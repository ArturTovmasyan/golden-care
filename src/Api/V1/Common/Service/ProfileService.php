<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Helper\UserAvatarHelper;
use App\Entity\User;
use App\Entity\UserPhone;

/**
 * Class ProfileService
 * @package App\Api\V1\Service
 */
class ProfileService extends BaseService
{
    /** @var UserAvatarHelper */
    private $userAvatarHelper;

    public function setUserAvatarHelper(UserAvatarHelper $userAvatarHelper)
    {
        $this->userAvatarHelper = $userAvatarHelper;
    }

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

            $user->setPhones($this->savePhones($user, $params['phones'] ?? []));

            $this->validate($user, null, ["api_profile_edit"]);

            if (!empty($params['avatar'])) {
                $this->userAvatarHelper->remove($user->getId());
                $this->userAvatarHelper->save($user->getId(), $params['avatar']);
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
     * Change User Password
     *
     * @param User $user
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function changePassword(User $loggedUser, array $params)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var User $user */
            $user = $this->em->getRepository(User::class)
                             ->findOneBy(['username' => $loggedUser->getUsername()]);

            $user->setOldPassword($params['password']);
            $user->setPlainPassword($params['new_password']);
            $user->setConfirmPassword($params['re_new_password']);

            $this->validate($user, null, ["api_profile_change_password"]);

            $encoded = $this->encoder->encodePassword($user, $params['new_password']);
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
     * @param User $user
     * @param array $phones
     * @return array
     */
    private function savePhones($user, array $phones = [])
    {
        if($user->getId()) {
            /**
             * @var UserPhone[] $oldPhones
             */
            $oldPhones = $this->em->getRepository(UserPhone::class)->findBy(['user' => $user]);

            foreach ($oldPhones as $phone) {
                $this->em->remove($phone);
            }
        }

        $hasPrimary = false;

        $userPhones = [];

        foreach($phones as $phone) {
            $userPhone = new UserPhone();
            $userPhone->setUser($user);
            $userPhone->setCompatibility($phone['compatibility'] ?? null);
            $userPhone->setType($phone['type']);
            $userPhone->setNumber($phone['number']);
            $userPhone->setPrimary((bool) $phone['primary'] ?? false);
            $userPhone->setSmsEnabled((bool) $phone['sms_enabled'] ?? false);

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
