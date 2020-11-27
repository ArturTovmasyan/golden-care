<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\LatePaymentNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\LatePayment;
use App\Entity\Space;
use App\Repository\LatePaymentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LatePaymentService
 * @package App\Api\V1\Admin\Service
 */
class LatePaymentService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var LatePaymentRepository $repo */
        $repo = $this->em->getRepository(LatePayment::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var LatePaymentRepository $repo */
        $repo = $this->em->getRepository(LatePayment::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LatePayment::class));
    }

    /**
     * @param $id
     * @return LatePayment|null|object
     */
    public function getById($id)
    {
        /** @var LatePaymentRepository $repo */
        $repo = $this->em->getRepository(LatePayment::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $id);
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

            $latePayment = new LatePayment();
            $latePayment->setTitle($params['title']);
            $latePayment->setSpace($space);

            $this->validate($latePayment, null, ['api_admin_late_payment_add']);

            $this->em->persist($latePayment);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $latePayment->getId();
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

            /** @var LatePaymentRepository $repo */
            $repo = $this->em->getRepository(LatePayment::class);

            /** @var LatePayment $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $id);

            if ($entity === null) {
                throw new LatePaymentNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_late_payment_edit']);

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

            /** @var LatePaymentRepository $repo */
            $repo = $this->em->getRepository(LatePayment::class);

            /** @var LatePayment $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $id);

            if ($entity === null) {
                throw new LatePaymentNotFoundException();
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
                throw new LatePaymentNotFoundException();
            }

            /** @var LatePaymentRepository $repo */
            $repo = $this->em->getRepository(LatePayment::class);

            $latePayments = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $ids);

            if (empty($latePayments)) {
                throw new LatePaymentNotFoundException();
            }

            /**
             * @var LatePayment $latePayment
             */
            foreach ($latePayments as $latePayment) {
                $this->em->remove($latePayment);
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
            throw new LatePaymentNotFoundException();
        }

        /** @var LatePaymentRepository $repo */
        $repo = $this->em->getRepository(LatePayment::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $ids);

        if (empty($entities)) {
            throw new LatePaymentNotFoundException();
        }

        return $this->getRelatedData(LatePayment::class, $entities);
    }
}
