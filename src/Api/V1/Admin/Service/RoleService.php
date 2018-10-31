<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\UserWithoutRoleException;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUserRole;

/**
 * Class RoleService
 * @package App\Api\V1\Service
 */
class RoleService extends BaseService
{
    /**
     * @return Role[]|array
     */
    public function getListing()
    {
        return $this->em->getRepository(Role::class)->findAll();
    }

    /**
     * @param $id
     * @return Role|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Role::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addRole(array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;
            $space   = null;

            if ($spaceId) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);


                if (is_null($space)) {
                    throw new SpaceNotFoundException();
                }
            }

            // save role
            $role = new Role();
            $role->setName($params['name'] ?? '');
            $role->setSpace($space);
            $role->setDefault((bool) $params['default']);
            $role->setSpaceDefault((bool) $params['space_default']);

            $this->validate($role, null, ["api_admin_role_add"]);

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
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editRole($id, array $params): void
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

            $spaceId = $params['space_id'] ?? 0;
            $space   = null;

            if ($spaceId) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if (is_null($space)) {
                    throw new SpaceNotFoundException();
                }
            }

            $role->setName($params['name'] ?? '');
            $role->setSpace($space);
            $role->setDefault((bool) $params['default']);
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

            $this->validate($role, null, ["api_admin_role_edit"]);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeRole($id): void
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