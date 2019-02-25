<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonRoleNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ResponsiblePersonRole;
use App\Entity\Space;
use App\Repository\ResponsiblePersonRoleRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResponsiblePersonRoleService
 * @package App\Api\V1\Admin\Service
 */
class ResponsiblePersonRoleService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ResponsiblePersonRoleRepository $repo */
        $repo = $this->em->getRepository(ResponsiblePersonRole::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ResponsiblePersonRoleRepository $repo */
        $repo = $this->em->getRepository(ResponsiblePersonRole::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class));
    }

    /**
     * @param $id
     * @return ResponsiblePersonRole|null|object
     */
    public function getById($id)
    {
        /** @var ResponsiblePersonRoleRepository $repo */
        $repo = $this->em->getRepository(ResponsiblePersonRole::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $responsiblePersonRole = new ResponsiblePersonRole();
            $responsiblePersonRole->setTitle($params['title']);
            $responsiblePersonRole->setSpace($space);

            $this->validate($responsiblePersonRole, null, ['api_admin_responsible_person_role_add']);

            $this->em->persist($responsiblePersonRole);
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
     * @throws \Exception
     */
    public function edit($id, array $params) : void
    {
        try {
            /**
             * @var ResponsiblePersonRole $entity
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            /** @var ResponsiblePersonRoleRepository $repo */
            $repo = $this->em->getRepository(ResponsiblePersonRole::class);

            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $id);

            if ($entity === null) {
                throw new ResponsiblePersonRoleNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_responsible_person_role_edit']);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResponsiblePersonRoleRepository $repo */
            $repo = $this->em->getRepository(ResponsiblePersonRole::class);

            /** @var ResponsiblePersonRole $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $id);

            if ($entity === null) {
                throw new ResponsiblePersonRoleNotFoundException();
            }

            $this->em->remove($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResponsiblePersonRoleNotFoundException();
            }

            /** @var ResponsiblePersonRoleRepository $repo */
            $repo = $this->em->getRepository(ResponsiblePersonRole::class);

            $responsiblePersonRoles = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $ids);

            if (empty($responsiblePersonRoles)) {
                throw new ResponsiblePersonRoleNotFoundException();
            }

            /**
             * @var ResponsiblePersonRole $responsiblePersonRole
             */
            foreach ($responsiblePersonRoles as $responsiblePersonRole) {
                $this->em->remove($responsiblePersonRole);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
