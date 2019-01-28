<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\UserWithoutRoleException;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUserRole;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RoleService
 * @package App\Api\V1\Service
 */
class RoleService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Role::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Role::class)->findAll();
    }

    /**
     * @param $id
     * @return Role|null|object
     */
    public function getById($id, GrantService $grantService)
    {
        /** @var Role $role */
        $role = $this->em->getRepository(Role::class)->find($id);

        if ($role) {
            $role->setGrants($grantService->getGrants($role->getGrants()));
        }

        return $role;
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(array $params): void
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
            $role->setGrants($params['grants'] ?? []);
            $role->setSpace($space);
            $role->setDefault((bool) $params['default']);
            $role->setSpaceDefault((bool) $params['space_default']);

            $this->validate($role, null, ["api_admin_role_add"]);

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
    public function edit($id, array $params): void
    {
        try {
            /**
             * @var Role $role
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
            $role->setGrants($params['grants'] ?? []);
            $role->setSpace($space);
            $role->setDefault((bool) $params['default']);
            $role->setSpaceDefault((bool) $params['space_default']);

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
    public function remove($id): void
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

            $this->em->remove($role);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new RoleNotFoundException();
            }

            $roles = $this->em->getRepository(Role::class)->findByIds($ids);

            if (empty($roles)) {
                throw new RoleNotFoundException();
            }

            /**
             * @var Role $role
             * @var SpaceUserRole $spaceUserRoles
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($roles as $role) {
                // check related users
                $spaceUserRoles = $role->getSpaceUserRoles();

                if ($spaceUserRoles->count()) {
                    throw new UserWithoutRoleException();
                }

                $this->em->remove($role);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (RoleNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
