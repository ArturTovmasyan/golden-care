<?php
namespace App\Api\V1\Common\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DifferentPasswordException;
use App\Api\V1\Common\Service\Exception\InvalidConfirmationTokenException;
use App\Api\V1\Common\Service\Exception\InvalidPasswordException;
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
     * @param User $user
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editUser(User $user, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $user->setFirstName($params['first_name']);
            $user->setLastName($params['last_name']);
            $user->setPhone($params['phone']);

            $this->validate($user, null, ["api_common_user_edit"]);

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

            $this->validate($user, null, ["api_common_user_change_password"]);

            $this->em->persist($user);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}