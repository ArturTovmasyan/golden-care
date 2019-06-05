<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Entity\User;
use App\Entity\UserImage;
use App\Entity\UserPhone;
use App\Repository\UserImageRepository;

/**
 * Class ProfileService
 * @package App\Api\V1\Service
 */
class ProfileService extends BaseService
{
    /**
     * @var ImageFilterService
     */
    private $imageFilterService;

    /**
     * @param ImageFilterService $imageFilterService
     */
    public function setImageFilterService(ImageFilterService $imageFilterService): void
    {
        $this->imageFilterService = $imageFilterService;
    }

    /**
     * @param User $user
     * @param array $params
     * @throws \Exception
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

            $this->validate($user, null, ['api_profile_edit']);

//            if (!empty($params['avatar'])) {
//                $this->userAvatarHelper->remove($user->getId());
//                $this->userAvatarHelper->save($user->getId(), $params['avatar']);
//            }

            $this->em->persist($user);

            // save photo
            if (!empty($params['avatar'])) {
                /** @var UserImageRepository $imageRepo */
                $imageRepo = $this->em->getRepository(UserImage::class);

                $image = $imageRepo->getBy($user->getId());

                if ($image === null) {
                    $image = new UserImage();
                }

                $image->setUser($user);
                $image->setPhoto($params['avatar']);

                $this->validate($user, null, ['api_admin_user_image_edit']);

                $this->em->persist($image);

                if ($image) {
                    $this->imageFilterService->createAllFilterVersion($image);
                }
            }

            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param User $loggedUser
     * @param array $params
     * @throws \Exception
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

            $this->validate($user, null, ['api_profile_change_password']);

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

    /**
     * @param User $loggedUser
     * @throws \Exception
     */
    public function acceptLicense(User $loggedUser)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var User $user */
            $user = $this->em->getRepository(User::class)
                ->findOneBy(['username' => $loggedUser->getUsername()]);

            $user->setLicenseAccepted(true);

            $this->em->persist($user);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param User $loggedUser
     * @throws \Exception
     */
    public function declineLicense(User $loggedUser)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var User $user */
            $user = $this->em->getRepository(User::class)
                ->findOneBy(['username' => $loggedUser->getUsername()]);

            $user->setLicenseAccepted(false);

            $this->em->persist($user);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
