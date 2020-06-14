<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\CurrentResidenceNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\CurrentResidence;
use App\Entity\Space;
use App\Repository\Lead\CurrentResidenceRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CurrentResidenceService
 * @package App\Api\V1\Admin\Service
 */
class CurrentResidenceService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var CurrentResidenceRepository $repo */
        $repo = $this->em->getRepository(CurrentResidence::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CurrentResidenceRepository $repo */
        $repo = $this->em->getRepository(CurrentResidence::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class));
    }

    /**
     * @param $id
     * @return CurrentResidence|null|object
     */
    public function getById($id)
    {
        /** @var CurrentResidenceRepository $repo */
        $repo = $this->em->getRepository(CurrentResidence::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $id);
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

            $currentResidence = new CurrentResidence();
            $currentResidence->setTitle($params['title']);
            $currentResidence->setSpace($space);

            $this->validate($currentResidence, null, ['api_lead_current_residence_add']);

            $this->em->persist($currentResidence);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $currentResidence->getId();
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

            /** @var CurrentResidenceRepository $repo */
            $repo = $this->em->getRepository(CurrentResidence::class);

            /** @var CurrentResidence $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $id);

            if ($entity === null) {
                throw new CurrentResidenceNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_current_residence_edit']);

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

            /** @var CurrentResidenceRepository $repo */
            $repo = $this->em->getRepository(CurrentResidence::class);

            /** @var CurrentResidence $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $id);

            if ($entity === null) {
                throw new CurrentResidenceNotFoundException();
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
                throw new CurrentResidenceNotFoundException();
            }

            /** @var CurrentResidenceRepository $repo */
            $repo = $this->em->getRepository(CurrentResidence::class);

            $currentResidences = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $ids);

            if (empty($currentResidences)) {
                throw new CurrentResidenceNotFoundException();
            }

            /**
             * @var CurrentResidence $currentResidence
             */
            foreach ($currentResidences as $currentResidence) {
                $this->em->remove($currentResidence);
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
            throw new CurrentResidenceNotFoundException();
        }

        /** @var CurrentResidenceRepository $repo */
        $repo = $this->em->getRepository(CurrentResidence::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $ids);

        if (empty($entities)) {
            throw new CurrentResidenceNotFoundException();
        }

        return $this->getRelatedData(CurrentResidence::class, $entities);
    }
}
