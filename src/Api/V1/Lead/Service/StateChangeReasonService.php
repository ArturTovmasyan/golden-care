<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\StateChangeReasonNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\StateChangeReason;
use App\Entity\Space;
use App\Repository\Lead\StateChangeReasonRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class StateChangeReasonService
 * @package App\Api\V1\Admin\Service
 */
class StateChangeReasonService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var StateChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StateChangeReason::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var StateChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StateChangeReason::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class));
    }

    /**
     * @param $id
     * @return StateChangeReason|null|object
     */
    public function getById($id)
    {
        /** @var StateChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StateChangeReason::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $id);
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

            $state = $params['state'] ? (int)$params['state'] : 0;

            $stateChangeReason = new StateChangeReason();
            $stateChangeReason->setTitle($params['title']);
            $stateChangeReason->setState($state);
            $stateChangeReason->setSpace($space);

            $this->validate($stateChangeReason, null, ['api_lead_state_change_reason_add']);

            $this->em->persist($stateChangeReason);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $stateChangeReason->getId();
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

            /** @var StateChangeReasonRepository $repo */
            $repo = $this->em->getRepository(StateChangeReason::class);

            /** @var StateChangeReason $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $id);

            if ($entity === null) {
                throw new StateChangeReasonNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $state = $params['state'] ? (int)$params['state'] : 0;

            $entity->setTitle($params['title']);
            $entity->setState($state);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_state_change_reason_edit']);

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

            /** @var StateChangeReasonRepository $repo */
            $repo = $this->em->getRepository(StateChangeReason::class);

            /** @var StateChangeReason $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $id);

            if ($entity === null) {
                throw new StateChangeReasonNotFoundException();
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
                throw new StateChangeReasonNotFoundException();
            }

            /** @var StateChangeReasonRepository $repo */
            $repo = $this->em->getRepository(StateChangeReason::class);

            $stateChangeReasons = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $ids);

            if (empty($stateChangeReasons)) {
                throw new StateChangeReasonNotFoundException();
            }

            /**
             * @var StateChangeReason $stateChangeReason
             */
            foreach ($stateChangeReasons as $stateChangeReason) {
                $this->em->remove($stateChangeReason);
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
            throw new StateChangeReasonNotFoundException();
        }

        /** @var StateChangeReasonRepository $repo */
        $repo = $this->em->getRepository(StateChangeReason::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $ids);

        if (empty($entities)) {
            throw new StateChangeReasonNotFoundException();
        }

        return $this->getRelatedData(StateChangeReason::class, $entities);
    }
}
