<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\StageChangeReasonNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\StageChangeReason;
use App\Entity\Space;
use App\Repository\Lead\StageChangeReasonRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class StageChangeReasonService
 * @package App\Api\V1\Admin\Service
 */
class StageChangeReasonService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var StageChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StageChangeReason::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var StageChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StageChangeReason::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class));
    }

    /**
     * @param $id
     * @return StageChangeReason|null|object
     */
    public function getById($id)
    {
        /** @var StageChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StageChangeReason::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $id);
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

            $stageChangeReason = new StageChangeReason();
            $stageChangeReason->setTitle($params['title']);
            $stageChangeReason->setSpace($space);

            $this->validate($stageChangeReason, null, ['api_lead_stage_change_reason_add']);

            $this->em->persist($stageChangeReason);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $stageChangeReason->getId();
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

            /** @var StageChangeReasonRepository $repo */
            $repo = $this->em->getRepository(StageChangeReason::class);

            /** @var StageChangeReason $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $id);

            if ($entity === null) {
                throw new StageChangeReasonNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_stage_change_reason_edit']);

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

            /** @var StageChangeReasonRepository $repo */
            $repo = $this->em->getRepository(StageChangeReason::class);

            /** @var StageChangeReason $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $id);

            if ($entity === null) {
                throw new StageChangeReasonNotFoundException();
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
                throw new StageChangeReasonNotFoundException();
            }

            /** @var StageChangeReasonRepository $repo */
            $repo = $this->em->getRepository(StageChangeReason::class);

            $stageChangeReasons = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $ids);

            if (empty($stageChangeReasons)) {
                throw new StageChangeReasonNotFoundException();
            }

            /**
             * @var StageChangeReason $stageChangeReason
             */
            foreach ($stageChangeReasons as $stageChangeReason) {
                $this->em->remove($stageChangeReason);
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
            throw new StageChangeReasonNotFoundException();
        }

        /** @var StageChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StageChangeReason::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StageChangeReason::class), $ids);

        if (empty($entities)) {
            throw new StageChangeReasonNotFoundException();
        }

        return $this->getRelatedData(StageChangeReason::class, $entities);
    }
}
