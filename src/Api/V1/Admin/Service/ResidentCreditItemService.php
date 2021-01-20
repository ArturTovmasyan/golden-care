<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CreditItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentCreditItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterValidThroughDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CreditItem;
use App\Entity\Resident;
use App\Entity\ResidentCreditItem;
use App\Entity\ResidentLedger;
use App\Repository\CreditItemRepository;
use App\Repository\ResidentCreditItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class ResidentCreditItemService
 * @package App\Api\V1\Admin\Service
 */
class ResidentCreditItemService extends BaseService implements IGridService
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
            ->where('rci.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentCreditItemRepository $repo */
        $repo = $this->em->getRepository(ResidentCreditItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentCreditItem|null|object
     */
    public function getById($id)
    {
        /** @var ResidentCreditItemRepository $repo */
        $repo = $this->em->getRepository(ResidentCreditItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws ConnectionException
     */
    public function add(array $params): ?int
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

            $creditItemId = $params['credit_item_id'] ?? 0;

            /** @var CreditItemRepository $creditItemRepo */
            $creditItemRepo = $this->em->getRepository(CreditItem::class);

            /** @var CreditItem $creditItem */
            $creditItem = $creditItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $creditItemId);

            if ($creditItem === null) {
                throw new CreditItemNotFoundException();
            }

            $residentCreditItem = new ResidentCreditItem();
            $residentCreditItem->setResident($resident);
            $residentCreditItem->setCreditItem($creditItem);
            $residentCreditItem->setAmount($params['amount']);

            $start = null;
            if (!empty($params['start'])) {
                $startDate = new \DateTime($params['start']);
                $start = new \DateTime($startDate->format('y-m-01'));
                $start->setTime(0, 0, 0);
            }

            $residentCreditItem->setStart($start);

            $end = null;
            if (!empty($params['end'])) {
                $endDate = new \DateTime($params['end']);
                $end = new \DateTime($endDate->format('y-m-t'));
                $end->setTime(23, 59, 59);

                if ($start > $end) {
                    throw new StartGreaterValidThroughDateException();
                }
            }

            $residentCreditItem->setEnd($end);

            $residentCreditItem->setNotes($params['notes']);

            $this->validate($residentCreditItem, null, ['api_admin_resident_credit_item_add']);

            $this->em->persist($residentCreditItem);
            $this->em->flush();

            //Re-Calculate Ledger
            $this->recalculateLedger($currentSpace, $residentId, $residentCreditItem->getStart(), $residentCreditItem->getEnd(), $residentCreditItem->getAmount(), 0);

            $this->em->getConnection()->commit();

            $insert_id = $residentCreditItem->getId();
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $currentSpace
     * @param $residentId
     * @param $startDate
     * @param $endDate
     * @param $newAmount
     * @param $oldAmount
     * @throws Exception
     */
    private function recalculateLedger($currentSpace, $residentId, $startDate, $endDate, $newAmount, $oldAmount): void
    {
        /** @var ResidentLedgerRepository $ledgerRepo */
        $ledgerRepo = $this->em->getRepository(ResidentLedger::class);
        $ledgers = $ledgerRepo->getResidentLedgersByDateInterval($currentSpace, null, $residentId, $startDate, $endDate);

        if (!empty($ledgers)) {
            /** @var ResidentLedger $ledger */
            foreach ($ledgers as $ledger) {
                $newPrivatePayBalanceDue = $ledger->getPrivatePayBalanceDue() - $newAmount + $oldAmount;
                $ledger->setPrivatePayBalanceDue(round($newPrivatePayBalanceDue, 2));

                $this->em->persist($ledger);
            }

            $this->em->flush();
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws ConnectionException
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            /** @var ResidentCreditItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $id);

            if ($entity === null) {
                throw new ResidentCreditItemNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $creditItemId = $params['credit_item_id'] ?? 0;

            /** @var CreditItemRepository $creditItemRepo */
            $creditItemRepo = $this->em->getRepository(CreditItem::class);

            /** @var CreditItem $creditItem */
            $creditItem = $creditItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $creditItemId);

            if ($creditItem === null) {
                throw new CreditItemNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setCreditItem($creditItem);
            $entity->setAmount($params['amount']);

            $start = null;
            if (!empty($params['start'])) {
                $startDate = new \DateTime($params['start']);
                $start = new \DateTime($startDate->format('y-m-01'));
                $start->setTime(0, 0, 0);
            }

            $entity->setStart($start);

            $end = null;
            if (!empty($params['end'])) {
                $endDate = new \DateTime($params['end']);
                $end = new \DateTime($endDate->format('y-m-t'));
                $end->setTime(23, 59, 59);

                if ($start > $end) {
                    throw new StartGreaterValidThroughDateException();
                }
            }

            $entity->setEnd($end);

            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_credit_item_edit']);

            $this->em->persist($entity);

            //Re-Calculate Ledger
            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();

            $entityChangeSet = $uow->getEntityChangeSet($entity);

            if (!empty($entityChangeSet) && array_key_exists('amount', $entityChangeSet)) {
                $this->recalculateLedger($currentSpace, $residentId, $entity->getStart(), $entity->getEnd(), $entityChangeSet['amount']['1'], $entityChangeSet['amount']['0']);
            }

            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (Exception $e) {
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            /** @var ResidentCreditItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $id);

            if ($entity === null) {
                throw new ResidentCreditItemNotFoundException();
            }

            $residentId = $entity->getResident() !== null ? $entity->getResident()->getId() : 0;

            //Re-Calculate Ledger
            $this->recalculateLedger($currentSpace, $residentId, $entity->getStart(), $entity->getEnd(), 0, $entity->getAmount());

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
                throw new ResidentCreditItemNotFoundException();
            }

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            $residentCreditItems = $repo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $ids);

            if (empty($residentCreditItems)) {
                throw new ResidentCreditItemNotFoundException();
            }

            /**
             * @var ResidentCreditItem $residentCreditItem
             */
            foreach ($residentCreditItems as $residentCreditItem) {
                $residentId = $residentCreditItem->getResident() !== null ? $residentCreditItem->getResident()->getId() : 0;

                //Re-Calculate Ledger
                $this->recalculateLedger($currentSpace, $residentId, $residentCreditItem->getStart(), $residentCreditItem->getEnd(), 0, $residentCreditItem->getAmount());

                $this->em->remove($residentCreditItem);
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
            throw new ResidentCreditItemNotFoundException();
        }

        /** @var ResidentCreditItemRepository $repo */
        $repo = $this->em->getRepository(ResidentCreditItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $ids);

        if (empty($entities)) {
            throw new ResidentCreditItemNotFoundException();
        }

        return $this->getRelatedData(ResidentCreditItem::class, $entities);
    }
}
