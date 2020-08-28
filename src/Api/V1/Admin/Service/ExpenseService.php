<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ExpenseNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Expense;
use App\Entity\Space;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ExpenseService
 * @package App\Api\V1\Admin\Service
 */
class ExpenseService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var ExpenseRepository $repo */
        $repo = $this->em->getRepository(Expense::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Expense::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ExpenseRepository $repo */
        $repo = $this->em->getRepository(Expense::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Expense::class));
    }

    /**
     * @param $id
     * @return Expense|null|object
     */
    public function getById($id)
    {
        /** @var ExpenseRepository $repo */
        $repo = $this->em->getRepository(Expense::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Expense::class), $id);
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

            $expense = new Expense();
            $expense->setTitle($params['title']);
            $expense->setSpace($space);

            $this->validate($expense, null, ['api_admin_expense_add']);

            $this->em->persist($expense);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $expense->getId();
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

            /** @var ExpenseRepository $repo */
            $repo = $this->em->getRepository(Expense::class);

            /** @var Expense $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Expense::class), $id);

            if ($entity === null) {
                throw new ExpenseNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_expense_edit']);

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

            /** @var ExpenseRepository $repo */
            $repo = $this->em->getRepository(Expense::class);

            /** @var Expense $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Expense::class), $id);

            if ($entity === null) {
                throw new ExpenseNotFoundException();
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
                throw new ExpenseNotFoundException();
            }

            /** @var ExpenseRepository $repo */
            $repo = $this->em->getRepository(Expense::class);

            $expenses = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Expense::class), $ids);

            if (empty($expenses)) {
                throw new ExpenseNotFoundException();
            }

            /**
             * @var Expense $expense
             */
            foreach ($expenses as $expense) {
                $this->em->remove($expense);
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
            throw new ExpenseNotFoundException();
        }

        /** @var ExpenseRepository $repo */
        $repo = $this->em->getRepository(Expense::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Expense::class), $ids);

        if (empty($entities)) {
            throw new ExpenseNotFoundException();
        }

        return $this->getRelatedData(Expense::class, $entities);
    }
}
