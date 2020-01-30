<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RentReasonNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\RentReason;
use App\Entity\Space;
use App\Repository\RentReasonRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RentReasonService
 * @package App\Api\V1\Admin\Service
 */
class RentReasonService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var RentReasonRepository $repo */
        $repo = $this->em->getRepository(RentReason::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RentReason::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var RentReasonRepository $repo */
        $repo = $this->em->getRepository(RentReason::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RentReason::class));
    }

    /**
     * @param $id
     * @return RentReason|null|object
     */
    public function getById($id)
    {
        /** @var RentReasonRepository $repo */
        $repo = $this->em->getRepository(RentReason::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RentReason::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
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

            $rentReason = new RentReason();
            $rentReason->setTitle($params['title']);
            $rentReason->setNotes($params['notes']);
            $rentReason->setSpace($space);

            $this->validate($rentReason, null, ['api_admin_rent_reason_add']);

            $this->em->persist($rentReason);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $rentReason->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var RentReasonRepository $repo */
            $repo = $this->em->getRepository(RentReason::class);

            /** @var RentReason $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RentReason::class), $id);

            if ($entity === null) {
                throw new RentReasonNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setNotes($params['notes']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_rent_reason_edit']);

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

            /** @var RentReasonRepository $repo */
            $repo = $this->em->getRepository(RentReason::class);

            /** @var RentReason $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RentReason::class), $id);

            if ($entity === null) {
                throw new RentReasonNotFoundException();
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
                throw new RentReasonNotFoundException();
            }

            /** @var RentReasonRepository $repo */
            $repo = $this->em->getRepository(RentReason::class);

            $rentReasons = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RentReason::class), $ids);

            if (empty($rentReasons)) {
                throw new RentReasonNotFoundException();
            }

            /**
             * @var RentReason $rentReason
             */
            foreach ($rentReasons as $rentReason) {
                $this->em->remove($rentReason);
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
            throw new RentReasonNotFoundException();
        }

        /** @var RentReasonRepository $repo */
        $repo = $this->em->getRepository(RentReason::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RentReason::class), $ids);

        if (empty($entities)) {
            throw new RentReasonNotFoundException();
        }

        return $this->getRelatedData(RentReason::class, $entities);
    }
}
