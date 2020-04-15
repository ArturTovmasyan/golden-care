<?php

namespace App\Api\V1\Common\Service;

use App\Api\V1\Admin\Service\UserService;
use App\Api\V1\Common\Service\Exception\FileExtensionException;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Entity\Image;
use App\Entity\User;
use App\Entity\UserPhone;
use App\Model\FileType;
use App\Util\MimeUtil;
use App\Util\StringUtil;
use DataURI\Parser;

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
     * @param UserService $userService
     * @param $id
     * @param bool $isMe
     * @return array
     */
    public function downloadFile($userService, $id, $isMe = false): array
    {
        $entity = $userService->getById($id);

        if (!empty($entity) && $entity->getImage() !== null) {
            return [strtolower($entity->getFirstName() . '_' . $entity->getLastName()), $entity->getImage()->getMimeType(), $this->s3Service->downloadFile($isMe ? $entity->getImage()->getS3Id3535() : $entity->getImage()->getS3Id150150(), $entity->getImage()->getType())];
        }

        return [null, null, null];
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

            $this->em->persist($user);

            $photo = !empty($params['avatar']) ? $params['avatar'] : null;

            // save image
            $this->saveImage($user, $photo);

            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param User $user
     * @param $photo
     */
    private function saveImage(User $user, $photo): void
    {
        $filterService = $this->container->getParameter('filter_service');

        $image = $user->getImage();
        if ($photo !== null) {
            if (!StringUtil::starts_with($photo, 'http')) {
                if ($image !== null) {
                    $this->s3Service->removeFile($image->getS3Id(), $image->getType());
                    $this->s3Service->removeFile($image->getS3Id3535(), $image->getType());
                    $this->s3Service->removeFile($image->getS3Id150150(), $image->getType());
                    $this->s3Service->removeFile($image->getS3Id300300(), $image->getType());
                } else {
                    $image = new Image();
                }

                $parseFile = Parser::parse($photo);
                $base64Image = $parseFile->getData();
                $mimeType = $parseFile->getMimeType();
                if ($mimeType === 'image/jpg') {
                    $mimeType = 'image/jpeg';
                }
                $format = MimeUtil::mime2ext($mimeType);

                $image->setMimeType($mimeType);
                $image->setType(FileType::TYPE_AVATAR);
                $image->setUser($user);

                $this->validate($image, null, ['api_admin_user_image_edit']);

                $this->em->persist($image);

                //validate image
                if (!\in_array($format, $filterService['extensions'], false)) {
                    throw new FileExtensionException();
                }

                $s3Id = $image->getId() . '.' . MimeUtil::mime2ext($image->getMimeType());
                $image->setS3Id($s3Id);
                $this->em->persist($image);

                $this->s3Service->uploadFile($photo, $s3Id, $image->getType(), $image->getMimeType());

                $this->imageFilterService->createAllFilterVersion($image, $base64Image, $mimeType, $format);

                //set S3 URI
                $s3Uri_150_150 = $this->s3Service->getFile($image->getS3Id150150(), $image->getType());
                $image->setS3Uri150150($s3Uri_150_150);

                $s3Uri = $this->s3Service->getFile($image->getS3Id(), $image->getType());
                $image->setS3Uri($s3Uri);

                $this->em->persist($image);
            }
        } elseif ($photo === null && $image !== null) {
            $this->s3Service->removeFile($image->getS3Id(), $image->getType());
            $this->s3Service->removeFile($image->getS3Id3535(), $image->getType());
            $this->s3Service->removeFile($image->getS3Id150150(), $image->getType());
            $this->s3Service->removeFile($image->getS3Id300300(), $image->getType());
            $this->em->remove($image);
        }
    }

    /**
     * @param User $loggedUser
     * @param array $params
     * @throws \Exception
     */
    public function changePassword(User $loggedUser, array $params): void
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
     * @return array|null
     */
    private function savePhones(User $user, array $phones = []): ?array
    {
        if ($user->getId()) {
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

        foreach ($phones as $phone) {
            $primary = $phone['primary'] ? (bool)$phone['primary'] : false;
            $smsEnabled = $phone['sms_enabled'] ? (bool)$phone['sms_enabled'] : false;

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
    public function acceptLicense(User $loggedUser): void
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
    public function declineLicense(User $loggedUser): void
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
