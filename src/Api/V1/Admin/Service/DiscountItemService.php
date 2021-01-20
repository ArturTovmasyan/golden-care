<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CanBeChangedIsRequiredException;
use App\Api\V1\Common\Service\Exception\DiscountItemNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\DiscountItem;
use App\Entity\Space;
use App\Repository\DiscountItemRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DiscountItemService
 * @package App\Api\V1\Admin\Service
 */
class DiscountItemService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var DiscountItemRepository $repo */
        $repo = $this->em->getRepository(DiscountItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var DiscountItemRepository $repo */
        $repo = $this->em->getRepository(DiscountItem::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiscountItem::class));
    }

    /**
     * @param $id
     * @return DiscountItem|null|object
     */
    public function getById($id)
    {
        /** @var DiscountItemRepository $repo */
        $repo = $this->em->getRepository(DiscountItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $id);
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

            $discountItem = new DiscountItem();
            $discountItem->setTitle($params['title']);
            $discountItem->setSpace($space);

            $amount = !empty($params['amount']) ? $params['amount'] : null;
            $discountItem->setAmount($amount);

            $canBeChanged = !empty($params['can_be_changed']) ? (bool)$params['can_be_changed'] : false;
            if (is_numeric($amount) && $canBeChanged === false) {
                throw new CanBeChangedIsRequiredException();
            }
            $discountItem->setCanBeChanged($canBeChanged);

            $this->validate($discountItem, null, ['api_admin_discount_item_add']);

            $this->em->persist($discountItem);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $discountItem->getId();
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

            /** @var DiscountItemRepository $repo */
            $repo = $this->em->getRepository(DiscountItem::class);

            /** @var DiscountItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $id);

            if ($entity === null) {
                throw new DiscountItemNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $amount = !empty($params['amount']) ? $params['amount'] : null;
            $entity->setAmount($amount);

            $canBeChanged = !empty($params['can_be_changed']) ? (bool)$params['can_be_changed'] : false;
            if (is_numeric($amount) && $canBeChanged === false) {
                throw new CanBeChangedIsRequiredException();
            }
            $entity->setCanBeChanged($canBeChanged);

            $this->validate($entity, null, ['api_admin_discount_item_edit']);

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

            /** @var DiscountItemRepository $repo */
            $repo = $this->em->getRepository(DiscountItem::class);

            /** @var DiscountItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $id);

            if ($entity === null) {
                throw new DiscountItemNotFoundException();
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
                throw new DiscountItemNotFoundException();
            }

            /** @var DiscountItemRepository $repo */
            $repo = $this->em->getRepository(DiscountItem::class);

            $discountItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $ids);

            if (empty($discountItems)) {
                throw new DiscountItemNotFoundException();
            }

            /**
             * @var DiscountItem $discountItem
             */
            foreach ($discountItems as $discountItem) {
                $this->em->remove($discountItem);
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
            throw new DiscountItemNotFoundException();
        }

        /** @var DiscountItemRepository $repo */
        $repo = $this->em->getRepository(DiscountItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $ids);

        if (empty($entities)) {
            throw new DiscountItemNotFoundException();
        }

        return $this->getRelatedData(DiscountItem::class, $entities);
    }
}