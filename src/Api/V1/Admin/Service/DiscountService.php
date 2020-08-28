<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiscountNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Discount;
use App\Entity\Space;
use App\Repository\DiscountRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DiscountService
 * @package App\Api\V1\Admin\Service
 */
class DiscountService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var DiscountRepository $repo */
        $repo = $this->em->getRepository(Discount::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Discount::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var DiscountRepository $repo */
        $repo = $this->em->getRepository(Discount::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Discount::class));
    }

    /**
     * @param $id
     * @return Discount|null|object
     */
    public function getById($id)
    {
        /** @var DiscountRepository $repo */
        $repo = $this->em->getRepository(Discount::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Discount::class), $id);
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

            $discount = new Discount();
            $discount->setTitle($params['title']);
            $discount->setSpace($space);

            $this->validate($discount, null, ['api_admin_discount_add']);

            $this->em->persist($discount);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $discount->getId();
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

            /** @var DiscountRepository $repo */
            $repo = $this->em->getRepository(Discount::class);

            /** @var Discount $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Discount::class), $id);

            if ($entity === null) {
                throw new DiscountNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_discount_edit']);

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

            /** @var DiscountRepository $repo */
            $repo = $this->em->getRepository(Discount::class);

            /** @var Discount $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Discount::class), $id);

            if ($entity === null) {
                throw new DiscountNotFoundException();
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
                throw new DiscountNotFoundException();
            }

            /** @var DiscountRepository $repo */
            $repo = $this->em->getRepository(Discount::class);

            $discounts = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Discount::class), $ids);

            if (empty($discounts)) {
                throw new DiscountNotFoundException();
            }

            /**
             * @var Discount $discount
             */
            foreach ($discounts as $discount) {
                $this->em->remove($discount);
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
            throw new DiscountNotFoundException();
        }

        /** @var DiscountRepository $repo */
        $repo = $this->em->getRepository(Discount::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Discount::class), $ids);

        if (empty($entities)) {
            throw new DiscountNotFoundException();
        }

        return $this->getRelatedData(Discount::class, $entities);
    }
}
