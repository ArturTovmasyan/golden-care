<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\FunnelStageNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\FunnelStage;
use App\Entity\Space;
use App\Repository\Lead\FunnelStageRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FunnelStageService
 * @package App\Api\V1\Admin\Service
 */
class FunnelStageService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var FunnelStageRepository $repo */
        $repo = $this->em->getRepository(FunnelStage::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var FunnelStageRepository $repo */
        $repo = $this->em->getRepository(FunnelStage::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class));
    }

    /**
     * @param $id
     * @return FunnelStage|null|object
     */
    public function getById($id)
    {
        /** @var FunnelStageRepository $repo */
        $repo = $this->em->getRepository(FunnelStage::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $funnelStage = new FunnelStage();
            $funnelStage->setTitle($params['title']);
            $funnelStage->setSeqNo($params['seq_no']);
            $funnelStage->setOpen($params['open']);
            $funnelStage->setSpace($space);

            $this->validate($funnelStage, null, ['api_lead_funnel_stage_add']);

            $this->em->persist($funnelStage);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $funnelStage->getId();
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
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var FunnelStageRepository $repo */
            $repo = $this->em->getRepository(FunnelStage::class);

            /** @var FunnelStage $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $id);

            if ($entity === null) {
                throw new FunnelStageNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSeqNo($params['seq_no']);
            $entity->setOpen($params['open']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_funnel_stage_edit']);

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

            /** @var FunnelStageRepository $repo */
            $repo = $this->em->getRepository(FunnelStage::class);

            /** @var FunnelStage $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $id);

            if ($entity === null) {
                throw new FunnelStageNotFoundException();
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
                throw new FunnelStageNotFoundException();
            }

            /** @var FunnelStageRepository $repo */
            $repo = $this->em->getRepository(FunnelStage::class);

            $funnelStages = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $ids);

            if (empty($funnelStages)) {
                throw new FunnelStageNotFoundException();
            }

            /**
             * @var FunnelStage $funnelStage
             */
            foreach ($funnelStages as $funnelStage) {
                $this->em->remove($funnelStage);
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
            throw new FunnelStageNotFoundException();
        }

        /** @var FunnelStageRepository $repo */
        $repo = $this->em->getRepository(FunnelStage::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $ids);

        if (empty($entities)) {
            throw new FunnelStageNotFoundException();
        }

        return $this->getRelatedData(FunnelStage::class, $entities);
    }
}
