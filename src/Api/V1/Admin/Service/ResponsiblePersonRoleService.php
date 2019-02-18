<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonRoleNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ResponsiblePersonRole;
use App\Entity\Space;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(ResponsiblePersonRole::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(ResponsiblePersonRole::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return ResponsiblePersonRole|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResponsiblePersonRole::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $spaceId = $params['space_id'] ?? 0;

            $space = $this->em->getRepository(Space::class)->find($spaceId);

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

            $entity = $this->em->getRepository(ResponsiblePersonRole::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new ResponsiblePersonRoleNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            $space = $this->em->getRepository(Space::class)->find($spaceId);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResponsiblePersonRole $entity */
            $entity = $this->em->getRepository(ResponsiblePersonRole::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResponsiblePersonRoleNotFoundException();
            }

            $responsiblePersonRoles = $this->em->getRepository(ResponsiblePersonRole::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

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
