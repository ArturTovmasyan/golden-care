<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ExpenseItemNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ExpenseItem;
use App\Entity\Space;
use App\Repository\ExpenseItemRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ExpenseItemService
 * @package App\Api\V1\Admin\Service
 */
class ExpenseItemService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var ExpenseItemRepository $repo */
        $repo = $this->em->getRepository(ExpenseItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ExpenseItemRepository $repo */
        $repo = $this->em->getRepository(ExpenseItem::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class));
    }

    /**
     * @param $id
     * @return ExpenseItem|null|object
     */
    public function getById($id)
    {
        /** @var ExpenseItemRepository $repo */
        $repo = $this->em->getRepository(ExpenseItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $id);
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

            $expenseItem = new ExpenseItem();
            $expenseItem->setTitle($params['title']);
            $expenseItem->setSpace($space);

            $this->validate($expenseItem, null, ['api_admin_expense_item_add']);

            $this->em->persist($expenseItem);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $expenseItem->getId();
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

            /** @var ExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ExpenseItem::class);

            /** @var ExpenseItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $id);

            if ($entity === null) {
                throw new ExpenseItemNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_expense_item_edit']);

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

            /** @var ExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ExpenseItem::class);

            /** @var ExpenseItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $id);

            if ($entity === null) {
                throw new ExpenseItemNotFoundException();
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
                throw new ExpenseItemNotFoundException();
            }

            /** @var ExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ExpenseItem::class);

            $expenseItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $ids);

            if (empty($expenseItems)) {
                throw new ExpenseItemNotFoundException();
            }

            /**
             * @var ExpenseItem $expenseItem
             */
            foreach ($expenseItems as $expenseItem) {
                $this->em->remove($expenseItem);
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
            throw new ExpenseItemNotFoundException();
        }

        /** @var ExpenseItemRepository $repo */
        $repo = $this->em->getRepository(ExpenseItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $ids);

        if (empty($entities)) {
            throw new ExpenseItemNotFoundException();
        }

        return $this->getRelatedData(ExpenseItem::class, $entities);
    }
}
