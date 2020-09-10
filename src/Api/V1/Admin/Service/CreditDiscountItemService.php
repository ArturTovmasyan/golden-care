<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CanBeChangedIsRequiredException;
use App\Api\V1\Common\Service\Exception\CreditDiscountItemNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CreditDiscountItem;
use App\Entity\Space;
use App\Repository\CreditDiscountItemRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CreditDiscountItemService
 * @package App\Api\V1\Admin\Service
 */
class CreditDiscountItemService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var CreditDiscountItemRepository $repo */
        $repo = $this->em->getRepository(CreditDiscountItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CreditDiscountItemRepository $repo */
        $repo = $this->em->getRepository(CreditDiscountItem::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class));
    }

    /**
     * @param $id
     * @return CreditDiscountItem|null|object
     */
    public function getById($id)
    {
        /** @var CreditDiscountItemRepository $repo */
        $repo = $this->em->getRepository(CreditDiscountItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $id);
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

            $creditDiscountItem = new CreditDiscountItem();
            $creditDiscountItem->setTitle($params['title']);
            $creditDiscountItem->setSpace($space);

            $amount = !empty($params['amount']) ? $params['amount'] : null;
            $creditDiscountItem->setAmount($amount);

            $canBeChanged = !empty($params['can_be_changed']) ? (bool)$params['can_be_changed'] : false;
            if (is_numeric($amount) && $canBeChanged === false) {
                throw new CanBeChangedIsRequiredException();
            }
            $creditDiscountItem->setCanBeChanged($canBeChanged);

            $date = $params['valid_through_date'];
            if (!empty($date)) {
                $date = new \DateTime($params['valid_through_date']);
                $date->setTime(0, 0, 0);

                $creditDiscountItem->setValidThroughDate($date);
            } else {
                $creditDiscountItem->setValidThroughDate(null);
            }

            $this->validate($creditDiscountItem, null, ['api_admin_credit_discount_item_add']);

            $this->em->persist($creditDiscountItem);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $creditDiscountItem->getId();
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

            /** @var CreditDiscountItemRepository $repo */
            $repo = $this->em->getRepository(CreditDiscountItem::class);

            /** @var CreditDiscountItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $id);

            if ($entity === null) {
                throw new CreditDiscountItemNotFoundException();
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

            $date = $params['valid_through_date'];
            if (!empty($date)) {
                $date = new \DateTime($params['valid_through_date']);
                $date->setTime(0, 0, 0);

                $entity->setValidThroughDate($date);
            } else {
                $entity->setValidThroughDate(null);
            }

            $this->validate($entity, null, ['api_admin_credit_discount_item_edit']);

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

            /** @var CreditDiscountItemRepository $repo */
            $repo = $this->em->getRepository(CreditDiscountItem::class);

            /** @var CreditDiscountItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $id);

            if ($entity === null) {
                throw new CreditDiscountItemNotFoundException();
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
                throw new CreditDiscountItemNotFoundException();
            }

            /** @var CreditDiscountItemRepository $repo */
            $repo = $this->em->getRepository(CreditDiscountItem::class);

            $creditDiscountItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $ids);

            if (empty($creditDiscountItems)) {
                throw new CreditDiscountItemNotFoundException();
            }

            /**
             * @var CreditDiscountItem $creditDiscountItem
             */
            foreach ($creditDiscountItems as $creditDiscountItem) {
                $this->em->remove($creditDiscountItem);
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
            throw new CreditDiscountItemNotFoundException();
        }

        /** @var CreditDiscountItemRepository $repo */
        $repo = $this->em->getRepository(CreditDiscountItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $ids);

        if (empty($entities)) {
            throw new CreditDiscountItemNotFoundException();
        }

        return $this->getRelatedData(CreditDiscountItem::class, $entities);
    }
}