<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceHaventAccessToRoleException;
use App\Api\V1\Common\Service\Exception\UserWithoutRoleException;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUserRole;

/**
 * Class RoleService
 * @package App\Api\V1\Dashboard\Service
 */
class RoleService extends BaseService
{
    /**
     * @param Space $space
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addRole(Space $space, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            // save role
            $role = new Role();
            $role->setName($params['name'] ?? '');
            $role->setSpace($space);
            $role->setDefault(0);
            $role->setSpaceDefault((bool) $params['space_default']);

            $this->validate($role, null, ["api_dashboard_role_add"]);

            // add permissions
            $permissionIds = array_unique($params['permissions']);
            $permissions   = $this->em->getRepository(Permission::class)->findById($permissionIds);

            if (!empty($permissions)) {
                $role->setPermissions($permissions);
            }

            $this->em->persist($role);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param Space $space
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editRole($id, Space $space, array $params): void
    {
        try {
            /**
             * @var Role $role
             * @var Permission $permission
             */
            $this->em->getConnection()->beginTransaction();

            $role = $this->em->getRepository(Role::class)->find($id);

            if (is_null($role)) {
                throw new RoleNotFoundException();
            }

            // edit role
            $role->setName($params['name'] ?? '');
            $role->setSpace($space);
            $role->setSpaceDefault((bool) $params['space_default']);

            // remove all role permissions
            $permissions = $role->getPermissions();
            foreach ($permissions as $permission) {
                $role->removePermission($permission);
            }

            // add permissions
            $permissionIds = array_unique($params['permissions']);
            $permissions   = $this->em->getRepository(Permission::class)->findById($permissionIds);

            if (!empty($permissions)) {
                $role->setPermissions($permissions);
            }

            $this->validate($role, null, ["api_dashboard_role_edit"]);

            $this->em->persist($role);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }


    /**
     * @param $id
     * @param Space $space
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeRole($id, Space $space): void
    {
        try {
            /**
             * @var Role $role
             * @var SpaceUserRole $spaceUserRoles
             */
            $this->em->getConnection()->beginTransaction();

            $role = $this->em->getRepository(Role::class)->find($id);

            if (is_null($role)) {
                throw new RoleNotFoundException();
            }

            $roleSpace = $role->getSpace();

            if (is_null($roleSpace) || $roleSpace->getId() != $space->getId()) {
                throw new SpaceHaventAccessToRoleException();
            }

            // check related users
            $spaceUserRoles = $role->getSpaceUserRoles();

            if ($spaceUserRoles->count()) {
                throw new UserWithoutRoleException();
            }

            // remove role permissions
            $permissions = $role->getPermissions();
            foreach ($permissions as $permission) {
                $role->removePermission($permission);
            }

            $this->em->remove($role);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}