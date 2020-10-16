<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CreditDiscountItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentCreditDiscountItemNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CreditDiscountItem;
use App\Entity\ResidentCreditDiscountItem;
use App\Entity\ResidentLedger;
use App\Repository\CreditDiscountItemRepository;
use App\Repository\ResidentCreditDiscountItemRepository;
use App\Repository\ResidentLedgerRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentCreditDiscountItemService
 * @package App\Api\V1\Admin\Service
 */
class ResidentCreditDiscountItemService extends BaseService implements IGridService
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
            ->where('rcdi.ledger = :ledgerId')
            ->setParameter('ledgerId', $ledgerId);

        /** @var ResidentCreditDiscountItemRepository $repo */
        $repo = $this->em->getRepository(ResidentCreditDiscountItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditDiscountItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentCreditDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditDiscountItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditDiscountItem::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentCreditDiscountItem|null|object
     */
    public function getById($id)
    {
        /** @var ResidentCreditDiscountItemRepository $repo */
        $repo = $this->em->getRepository(ResidentCreditDiscountItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditDiscountItem::class), $id);
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

            $creditDiscountItemId = $params['credit_discount_item_id'] ?? 0;

            /** @var CreditDiscountItemRepository $creditDiscountItemRepo */
            $creditDiscountItemRepo = $this->em->getRepository(CreditDiscountItem::class);

            /** @var CreditDiscountItem $creditDiscountItem */
            $creditDiscountItem = $creditDiscountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $creditDiscountItemId);

            if ($creditDiscountItem === null) {
                throw new CreditDiscountItemNotFoundException();
            }

            $residentCreditDiscountItem = new ResidentCreditDiscountItem();
            $residentCreditDiscountItem->setLedger($ledger);
            $residentCreditDiscountItem->setCreditDiscountItem($creditDiscountItem);
            $residentCreditDiscountItem->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $residentCreditDiscountItem->setDate($date);
            $residentCreditDiscountItem->setNotes($params['notes']);

            $this->validate($residentCreditDiscountItem, null, ['api_admin_resident_credit_discount_item_add']);

            $this->em->persist($residentCreditDiscountItem);

            //Re-Calculate Ledger Balance Due
            $oldBalanceDue = $ledger->getBalanceDue();
            $newBalanceDue = $oldBalanceDue + $residentCreditDiscountItem->getAmount();
            $ledger->setBalanceDue($newBalanceDue);
            $this->em->persist($ledger);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentCreditDiscountItem->getId();
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

            /** @var ResidentCreditDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditDiscountItem::class);

            /** @var ResidentCreditDiscountItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentCreditDiscountItem::class), $id);

            if ($entity === null) {
                throw new ResidentCreditDiscountItemNotFoundException();
            }

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $creditDiscountItemId = $params['credit_discount_item_id'] ?? 0;

            /** @var CreditDiscountItemRepository $creditDiscountItemRepo */
            $creditDiscountItemRepo = $this->em->getRepository(CreditDiscountItem::class);

            /** @var CreditDiscountItem $creditDiscountItem */
            $creditDiscountItem = $creditDiscountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditDiscountItem::class), $creditDiscountItemId);

            if ($creditDiscountItem === null) {
                throw new CreditDiscountItemNotFoundException();
            }

            $entity->setLedger($ledger);
            $entity->setCreditDiscountItem($creditDiscountItem);
            $entity->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_credit_discount_item_edit']);

            $this->em->persist($entity);

            //Re-Calculate Ledger Balance Due
            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();

            $changeSet = $this->em->getUnitOfWork()->getEntityChangeSet($entity);

            if (!empty($changeSet) && array_key_exists('amount', $changeSet)) {
                $oldBalanceDue = $ledger->getBalanceDue();
                $newBalanceDue = $oldBalanceDue + $changeSet['amount']['1'] - $changeSet['amount']['0'];
                $ledger->setBalanceDue($newBalanceDue);
                $this->em->persist($ledger);
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

            /** @var ResidentCreditDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditDiscountItem::class);

            /** @var ResidentCreditDiscountItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditDiscountItem::class), $id);

            if ($entity === null) {
                throw new ResidentCreditDiscountItemNotFoundException();
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
                throw new ResidentCreditDiscountItemNotFoundException();
            }

            /** @var ResidentCreditDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditDiscountItem::class);

            $residentCreditDiscountItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditDiscountItem::class), $ids);

            if (empty($residentCreditDiscountItems)) {
                throw new ResidentCreditDiscountItemNotFoundException();
            }

            /**
             * @var ResidentCreditDiscountItem $residentCreditDiscountItem
             */
            foreach ($residentCreditDiscountItems as $residentCreditDiscountItem) {
                $this->em->remove($residentCreditDiscountItem);
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
            throw new ResidentCreditDiscountItemNotFoundException();
        }

        /** @var ResidentCreditDiscountItemRepository $repo */
        $repo = $this->em->getRepository(ResidentCreditDiscountItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditDiscountItem::class), $ids);

        if (empty($entities)) {
            throw new ResidentCreditDiscountItemNotFoundException();
        }

        return $this->getRelatedData(ResidentCreditDiscountItem::class, $entities);
    }
}
