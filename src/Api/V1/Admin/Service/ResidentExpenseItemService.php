<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ExpenseItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentExpenseItemNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ExpenseItem;
use App\Entity\ResidentExpenseItem;
use App\Entity\ResidentLedger;
use App\Repository\ExpenseItemRepository;
use App\Repository\ResidentExpenseItemRepository;
use App\Repository\ResidentLedgerRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentExpenseItemService
 * @package App\Api\V1\Admin\Service
 */
class ResidentExpenseItemService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['ledger_id'])) {
            throw new ResidentLedgerNotFoundException();
        }

        $ledgerId = $params[0]['ledger_id'];

        $queryBuilder
            ->where('rei.ledger = :ledgerId')
            ->setParameter('ledgerId', $ledgerId);

        /** @var ResidentExpenseItemRepository $repo */
        $repo = $this->em->getRepository(ResidentExpenseItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ResidentExpenseItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentExpenseItem|null|object
     */
    public function getById($id)
    {
        /** @var ResidentExpenseItemRepository $repo */
        $repo = $this->em->getRepository(ResidentExpenseItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $id);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $expenseItemId = $params['expense_item_id'] ?? 0;

            /** @var ExpenseItemRepository $expenseItemRepo */
            $expenseItemRepo = $this->em->getRepository(ExpenseItem::class);

            /** @var ExpenseItem $expenseItem */
            $expenseItem = $expenseItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $expenseItemId);

            if ($expenseItem === null) {
                throw new ExpenseItemNotFoundException();
            }

            $residentExpenseItem = new ResidentExpenseItem();
            $residentExpenseItem->setLedger($ledger);
            $residentExpenseItem->setExpenseItem($expenseItem);
            $residentExpenseItem->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $residentExpenseItem->setDate($date);
            $residentExpenseItem->setNotes($params['notes']);

            $this->validate($residentExpenseItem, null, ['api_admin_resident_expense_item_add']);

            $this->em->persist($residentExpenseItem);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentExpenseItem->getId();
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ResidentExpenseItem::class);

            /** @var ResidentExpenseItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $id);

            if ($entity === null) {
                throw new ResidentExpenseItemNotFoundException();
            }

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $expenseItemId = $params['expense_item_id'] ?? 0;

            /** @var ExpenseItemRepository $expenseItemRepo */
            $expenseItemRepo = $this->em->getRepository(ExpenseItem::class);

            /** @var ExpenseItem $expenseItem */
            $expenseItem = $expenseItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $expenseItemId);

            if ($expenseItem === null) {
                throw new ExpenseItemNotFoundException();
            }

            $entity->setLedger($ledger);
            $entity->setExpenseItem($expenseItem);
            $entity->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_expense_item_edit']);

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

            /** @var ResidentExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ResidentExpenseItem::class);

            /** @var ResidentExpenseItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $id);

            if ($entity === null) {
                throw new ResidentExpenseItemNotFoundException();
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
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentExpenseItemNotFoundException();
            }

            /** @var ResidentExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ResidentExpenseItem::class);

            $residentExpenseItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $ids);

            if (empty($residentExpenseItems)) {
                throw new ResidentExpenseItemNotFoundException();
            }

            /**
             * @var ResidentExpenseItem $residentExpenseItem
             */
            foreach ($residentExpenseItems as $residentExpenseItem) {
                $this->em->remove($residentExpenseItem);
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
            throw new ResidentExpenseItemNotFoundException();
        }

        /** @var ResidentExpenseItemRepository $repo */
        $repo = $this->em->getRepository(ResidentExpenseItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $ids);

        if (empty($entities)) {
            throw new ResidentExpenseItemNotFoundException();
        }

        return $this->getRelatedData(ResidentExpenseItem::class, $entities);
    }
}
