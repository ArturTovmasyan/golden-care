<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\ActivityStatusNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Space;
use App\Repository\Lead\ActivityStatusRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ActivityStatusService
 * @package App\Api\V1\Admin\Service
 */
class ActivityStatusService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var ActivityStatusRepository $repo */
        $repo = $this->em->getRepository(ActivityStatus::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ActivityStatusRepository $repo */
        $repo = $this->em->getRepository(ActivityStatus::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class));
    }

    /**
     * @param $id
     * @return ActivityStatus|null|object
     */
    public function getById($id)
    {
        /** @var ActivityStatusRepository $repo */
        $repo = $this->em->getRepository(ActivityStatus::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $activityStatus = new ActivityStatus();
            $activityStatus->setTitle($params['title']);
            $activityStatus->setDone($params['done']);
            $activityStatus->setSpace($space);

            $this->validate($activityStatus, null, ['api_lead_activity_status_add']);

            $this->em->persist($activityStatus);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $activityStatus->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var ActivityStatusRepository $repo */
            $repo = $this->em->getRepository(ActivityStatus::class);

            /** @var ActivityStatus $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $id);

            if ($entity === null) {
                throw new ActivityStatusNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }


            $entity->setTitle($params['title']);
            $entity->setDone($params['done']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_activity_status_edit']);

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

            /** @var ActivityStatusRepository $repo */
            $repo = $this->em->getRepository(ActivityStatus::class);

            /** @var ActivityStatus $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $id);

            if ($entity === null) {
                throw new ActivityStatusNotFoundException();
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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ActivityStatusNotFoundException();
            }

            /** @var ActivityStatusRepository $repo */
            $repo = $this->em->getRepository(ActivityStatus::class);

            $activityStatuses = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $ids);

            if (empty($activityStatuses)) {
                throw new ActivityStatusNotFoundException();
            }

            /**
             * @var ActivityStatus $activityStatus
             */
            foreach ($activityStatuses as $activityStatus) {
                $this->em->remove($activityStatus);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new ActivityStatusNotFoundException();
        }

        /** @var ActivityStatusRepository $repo */
        $repo = $this->em->getRepository(ActivityStatus::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $ids);

        if (empty($entities)) {
            throw new ActivityStatusNotFoundException();
        }

        return $this->getRelatedData(ActivityStatus::class, $entities);
    }
}
