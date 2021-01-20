<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ExpenseItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentExpenseItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ExpenseItem;
use App\Entity\Resident;
use App\Entity\ResidentExpenseItem;
use App\Entity\ResidentLedger;
use App\Repository\ExpenseItemRepository;
use App\Repository\ResidentExpenseItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Throwable;

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
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rei.resident = :residentId')
            ->setParameter('residentId', $residentId);

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
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ResidentExpenseItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $residentId);
        }

        throw new ResidentNotFoundException();
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
     * @param ResidentLedgerService $residentLedgerService
     * @param array $params
     * @return int|null
     * @throws ConnectionException
     * @throws NonUniqueResultException
     */
    public function add(ResidentLedgerService $residentLedgerService, array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
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
            $residentExpenseItem->setResident($resident);
            $residentExpenseItem->setExpenseItem($expenseItem);
            $residentExpenseItem->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);
            }

            $residentExpenseItem->setDate($date);
            $residentExpenseItem->setNotes($params['notes']);

            $this->validate($residentExpenseItem, null, ['api_admin_resident_expense_item_add']);

            $this->em->persist($residentExpenseItem);
            $this->em->flush();

            //Re-Calculate Ledger
            $this->recalculateLedger($residentLedgerService, $currentSpace, $residentId,  $residentExpenseItem->getDate());

            $this->em->getConnection()->commit();

            $insert_id = $residentExpenseItem->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param ResidentLedgerService $residentLedgerService
     * @param $currentSpace
     * @param $residentId
     * @param $date
     * @throws NonUniqueResultException
     */
    private function recalculateLedger(ResidentLedgerService $residentLedgerService, $currentSpace, $residentId, $date): void
    {
        $dateStartFormatted = $date->format('m/01/Y 00:00:00');
        $dateEndFormatted = $date->format('m/t/Y 23:59:59');
        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);

        /** @var ResidentLedgerRepository $ledgerRepo */
        $ledgerRepo = $this->em->getRepository(ResidentLedger::class);
        /** @var ResidentLedger $ledger */
        $ledger = $ledgerRepo->getResidentLedgerByDate($currentSpace, null, $residentId, $dateStart, $dateEnd);

        if ($ledger !== null) {
            $recalculateLedger = $residentLedgerService->calculateLedgerData($currentSpace, $ledgerRepo, $ledger, $residentId);

            $this->em->persist($recalculateLedger);

            $this->em->flush();
        }
    }

    /**
     * @param $id
     * @param ResidentLedgerService $residentLedgerService
     * @param array $params
     * @throws ConnectionException
     * @throws NonUniqueResultException
     */
    public function edit($id, ResidentLedgerService $residentLedgerService, array $params): void
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

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $expenseItemId = $params['expense_item_id'] ?? 0;

            /** @var ExpenseItemRepository $expenseItemRepo */
            $expenseItemRepo = $this->em->getRepository(ExpenseItem::class);

            /** @var ExpenseItem $expenseItem */
            $expenseItem = $expenseItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $expenseItemId);

            if ($expenseItem === null) {
                throw new ExpenseItemNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setExpenseItem($expenseItem);
            $entity->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);
            }

            $entity->setDate($date);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_expense_item_edit']);

            $this->em->persist($entity);
            $this->em->flush();

            //Re-Calculate Ledger
            $this->recalculateLedger($residentLedgerService, $currentSpace, $residentId, $entity->getDate());

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param ResidentLedgerService $residentLedgerService
     * @throws ConnectionException
     * @throws NonUniqueResultException
     * @throws Throwable
     */
    public function remove($id, ResidentLedgerService $residentLedgerService)
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

            $residentId = $entity->getResident() !== null ? $entity->getResident()->getId() : 0;
            $date = $entity->getDate();

            $this->em->remove($entity);
            $this->em->flush();

            //Re-Calculate Ledger
            $this->recalculateLedger($residentLedgerService, $currentSpace, $residentId, $date);

            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @param ResidentLedgerService $residentLedgerService
     * @throws ConnectionException
     * @throws Throwable
     */
    public function removeBulk(array $ids, ResidentLedgerService $residentLedgerService): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentExpenseItemNotFoundException();
            }

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentExpenseItemRepository $repo */
            $repo = $this->em->getRepository(ResidentExpenseItem::class);

            $residentExpenseItems = $repo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentExpenseItem::class), $ids);

            if (empty($residentExpenseItems)) {
                throw new ResidentExpenseItemNotFoundException();
            }

            $dates = [];
            $residentId = 0;
            /**
             * @var ResidentExpenseItem $residentExpenseItem
             */
            foreach ($residentExpenseItems as $residentExpenseItem) {
                $residentId = $residentExpenseItem->getResident() !== null ? $residentExpenseItem->getResident()->getId() : 0;
                $dates[] = $residentExpenseItem->getDate();

                $this->em->remove($residentExpenseItem);
            }

            $this->em->flush();

            if (!empty($dates)) {
                foreach ($dates as $date) {
                    //Re-Calculate Ledger
                    $this->recalculateLedger($residentLedgerService, $currentSpace, $residentId, $date);
                }
            }

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
