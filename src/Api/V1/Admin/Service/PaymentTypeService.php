<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PaymentTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\PaymentType;
use App\Entity\Space;
use App\Repository\PaymentTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PaymentTypeService
 * @package App\Api\V1\Admin\Service
 */
class PaymentTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var PaymentTypeRepository $repo */
        $repo = $this->em->getRepository(PaymentType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var PaymentTypeRepository $repo */
        $repo = $this->em->getRepository(PaymentType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentType::class));
    }

    /**
     * @param $id
     * @return PaymentType|null|object
     */
    public function getById($id)
    {
        /** @var PaymentTypeRepository $repo */
        $repo = $this->em->getRepository(PaymentType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentType::class), $id);
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

            $paymentType = new PaymentType();
            $paymentType->setTitle($params['title']);
            $paymentType->setSpace($space);

            $this->validate($paymentType, null, ['api_admin_payment_type_add']);

            $this->em->persist($paymentType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $paymentType->getId();
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

            /** @var PaymentTypeRepository $repo */
            $repo = $this->em->getRepository(PaymentType::class);

            /** @var PaymentType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentType::class), $id);

            if ($entity === null) {
                throw new PaymentTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_payment_type_edit']);

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

            /** @var PaymentTypeRepository $repo */
            $repo = $this->em->getRepository(PaymentType::class);

            /** @var PaymentType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentType::class), $id);

            if ($entity === null) {
                throw new PaymentTypeNotFoundException();
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
                throw new PaymentTypeNotFoundException();
            }

            /** @var PaymentTypeRepository $repo */
            $repo = $this->em->getRepository(PaymentType::class);

            $paymentTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentType::class), $ids);

            if (empty($paymentTypes)) {
                throw new PaymentTypeNotFoundException();
            }

            /**
             * @var PaymentType $paymentType
             */
            foreach ($paymentTypes as $paymentType) {
                $this->em->remove($paymentType);
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
            throw new PaymentTypeNotFoundException();
        }

        /** @var PaymentTypeRepository $repo */
        $repo = $this->em->getRepository(PaymentType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(PaymentType::class), $ids);

        if (empty($entities)) {
            throw new PaymentTypeNotFoundException();
        }

        return $this->getRelatedData(PaymentType::class, $entities);
    }
}
