<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiscountItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentDiscountItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\DiscountItem;
use App\Entity\Resident;
use App\Entity\ResidentDiscountItem;
use App\Entity\ResidentLedger;
use App\Repository\DiscountItemRepository;
use App\Repository\ResidentDiscountItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * Class ResidentDiscountItemService
 * @package App\Api\V1\Admin\Service
 */
class ResidentDiscountItemService extends BaseService implements IGridService
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
            ->where('rdi.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentDiscountItemRepository $repo */
        $repo = $this->em->getRepository(ResidentDiscountItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentDiscountItem|null|object
     */
    public function getById($id)
    {
        /** @var ResidentDiscountItemRepository $repo */
        $repo = $this->em->getRepository(ResidentDiscountItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $id);
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

            $discountItemId = $params['discount_item_id'] ?? 0;

            /** @var DiscountItemRepository $discountItemRepo */
            $discountItemRepo = $this->em->getRepository(DiscountItem::class);

            /** @var DiscountItem $discountItem */
            $discountItem = $discountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $discountItemId);

            if ($discountItem === null) {
                throw new DiscountItemNotFoundException();
            }

            $residentDiscountItem = new ResidentDiscountItem();
            $residentDiscountItem->setResident($resident);
            $residentDiscountItem->setDiscountItem($discountItem);
            $residentDiscountItem->setAmount($params['amount']);

            $start = null;
            if (!empty($params['start'])) {
                $startDate = new \DateTime($params['start']);
                $start = new \DateTime($startDate->format('y-m-01'));
                $start->setTime(0, 0, 0);
            }

            $residentDiscountItem->setStart($start);

            $end = null;
            if (!empty($params['end'])) {
                $endDate = new \DateTime($params['end']);
                $end = new \DateTime($endDate->format('y-m-t'));
                $end->setTime(23, 59, 59);

                if ($start > $end) {
                    throw new StartGreaterEndDateException();
                }
            }

            $residentDiscountItem->setEnd($end);

            $residentDiscountItem->setNotes($params['notes']);

            $this->validate($residentDiscountItem, null, ['api_admin_resident_discount_item_add']);

            $this->em->persist($residentDiscountItem);
            $this->em->flush();

            //Re-Calculate Ledger
            $this->recalculateLedger($currentSpace, $residentId, $residentDiscountItem->getStart(), $residentDiscountItem->getEnd(), $residentDiscountItem->getAmount(), 0);

            $this->em->getConnection()->commit();

            $insert_id = $residentDiscountItem->getId();
        } catch (\Exception $e) {
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

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            /** @var ResidentDiscountItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $id);

            if ($entity === null) {
                throw new ResidentDiscountItemNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $discountItemId = $params['discount_item_id'] ?? 0;

            /** @var DiscountItemRepository $discountItemRepo */
            $discountItemRepo = $this->em->getRepository(DiscountItem::class);

            /** @var DiscountItem $discountItem */
            $discountItem = $discountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $discountItemId);

            if ($discountItem === null) {
                throw new DiscountItemNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setDiscountItem($discountItem);
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
                    throw new StartGreaterEndDateException();
                }
            }

            $entity->setEnd($end);

            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_discount_item_edit']);

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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            /** @var ResidentDiscountItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $id);

            if ($entity === null) {
                throw new ResidentDiscountItemNotFoundException();
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
                throw new ResidentDiscountItemNotFoundException();
            }

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            $residentDiscountItems = $repo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $ids);

            if (empty($residentDiscountItems)) {
                throw new ResidentDiscountItemNotFoundException();
            }

            /**
             * @var ResidentDiscountItem $residentDiscountItem
             */
            foreach ($residentDiscountItems as $residentDiscountItem) {
                $residentId = $residentDiscountItem->getResident() !== null ? $residentDiscountItem->getResident()->getId() : 0;

                //Re-Calculate Ledger
                $this->recalculateLedger($currentSpace, $residentId, $residentDiscountItem->getStart(), $residentDiscountItem->getEnd(), 0, $residentDiscountItem->getAmount());

                $this->em->remove($residentDiscountItem);
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
            throw new ResidentDiscountItemNotFoundException();
        }

        /** @var ResidentDiscountItemRepository $repo */
        $repo = $this->em->getRepository(ResidentDiscountItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $ids);

        if (empty($entities)) {
            throw new ResidentDiscountItemNotFoundException();
        }

        return $this->getRelatedData(ResidentDiscountItem::class, $entities);
    }
}
