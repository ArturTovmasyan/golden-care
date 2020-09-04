<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RpPaymentTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\RpPaymentType;
use App\Entity\Space;
use App\Repository\RpPaymentTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class RpPaymentTypeService
 * @package App\Api\V1\Admin\Service
 */
class RpPaymentTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var RpPaymentTypeRepository $repo */
        $repo = $this->em->getRepository(RpPaymentType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var RpPaymentTypeRepository $repo */
        $repo = $this->em->getRepository(RpPaymentType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class));
    }

    /**
     * @param $id
     * @return RpPaymentType|null|object
     */
    public function getById($id)
    {
        /** @var RpPaymentTypeRepository $repo */
        $repo = $this->em->getRepository(RpPaymentType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $id);
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

            $rpPaymentType = new RpPaymentType();
            $rpPaymentType->setTitle($params['title']);
            $rpPaymentType->setSpace($space);

            $this->validate($rpPaymentType, null, ['api_admin_rp_payment_type_add']);

            $this->em->persist($rpPaymentType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $rpPaymentType->getId();
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

            /** @var RpPaymentTypeRepository $repo */
            $repo = $this->em->getRepository(RpPaymentType::class);

            /** @var RpPaymentType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $id);

            if ($entity === null) {
                throw new RpPaymentTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_rp_payment_type_edit']);

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

            /** @var RpPaymentTypeRepository $repo */
            $repo = $this->em->getRepository(RpPaymentType::class);

            /** @var RpPaymentType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $id);

            if ($entity === null) {
                throw new RpPaymentTypeNotFoundException();
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
                throw new RpPaymentTypeNotFoundException();
            }

            /** @var RpPaymentTypeRepository $repo */
            $repo = $this->em->getRepository(RpPaymentType::class);

            $rpPaymentTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $ids);

            if (empty($rpPaymentTypes)) {
                throw new RpPaymentTypeNotFoundException();
            }

            /**
             * @var RpPaymentType $rpPaymentType
             */
            foreach ($rpPaymentTypes as $rpPaymentType) {
                $this->em->remove($rpPaymentType);
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
            throw new RpPaymentTypeNotFoundException();
        }

        /** @var RpPaymentTypeRepository $repo */
        $repo = $this->em->getRepository(RpPaymentType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $ids);

        if (empty($entities)) {
            throw new RpPaymentTypeNotFoundException();
        }

        return $this->getRelatedData(RpPaymentType::class, $entities);
    }
}
