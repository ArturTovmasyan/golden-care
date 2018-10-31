<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceUserNotFoundException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUser;
use App\Entity\SpaceUserRole;
use App\Entity\User;

/**
 * Class SpaceUserRoleService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceUserRoleService extends BaseService
{
    /**
     * @param Space $space
     * @param $userId
     * @param $roleId
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function changeRole(Space $space, $userId, $roleId)
    {
        try {
            /**
             * @var SpaceUserRole $spaceUserRole
             * @var SpaceUser     $spaceUser
             * @var User          $user
             * @var Role          $role
             */
            $this->em->getConnection()->beginTransaction();

            $role  = $this->em->getRepository(Role::class)->find($roleId);
            $user  = $this->em->getRepository(User::class)->find($userId);

            if (is_null($role)) {
                throw new RoleNotFoundException();
            }

            if (is_null($user)) {
                throw new UserNotFoundException();
            }

            $spaceUser = $this->em->getRepository(SpaceUser::class)->findOneBy(
                [
                    'space' => $space,
                    'user'  => $user,
                ]
            );

            if (is_null($spaceUser)) {
                throw new SpaceUserNotFoundException();
            }

            $spaceUserRole = $this->em->getRepository(SpaceUserRole::class)->findOneBy(
                [
                    'space' => $space,
                    'user'  => $user,
                ]
            );

            if (!is_null($spaceUserRole)) {
                $this->em->remove($spaceUserRole);
            }

            // save relation
            $spaceUserRole = new SpaceUserRole();
            $spaceUserRole->setUser($user);
            $spaceUserRole->setRole($role);
            $spaceUserRole->setSpace($space);
            $this->em->persist($spaceUserRole);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

}