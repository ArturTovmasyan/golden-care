<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SpaceUserNotFoundException;
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
     * @param User $user
     * @param Role $role
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function changeRole(Space $space, User $user, Role $role)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $spaceUser = $this->em->getRepository(SpaceUser::class)->findOneBy(
                [
                    'space' => $space,
                    'user' => $user,
                ]
            );

            if (is_null($spaceUser)) {
                throw new SpaceUserNotFoundException();
            }

            $spaceUserRole = $this->em->getRepository(SpaceUserRole::class)->findOneBy(
                [
                    'space' => $space,
                    'user' => $user,
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
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

}