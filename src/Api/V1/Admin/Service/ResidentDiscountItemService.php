<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiscountItemNotFoundException;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentDiscountItemNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\DiscountItem;
use App\Entity\ResidentDiscountItem;
use App\Entity\ResidentLedger;
use App\Repository\DiscountItemRepository;
use App\Repository\ResidentDiscountItemRepository;
use App\Repository\ResidentLedgerRepository;
use Doctrine\ORM\QueryBuilder;

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
        if (empty($params) || empty($params[0]['ledger_id'])) {
            throw new ResidentLedgerNotFoundException();
        }

        $ledgerId = $params[0]['ledger_id'];

        $queryBuilder
            ->where('rdi.ledger = :ledgerId')
            ->setParameter('ledgerId', $ledgerId);

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
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
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

            $discountItemId = $params['discount_item_id'] ?? 0;

            /** @var DiscountItemRepository $discountItemRepo */
            $discountItemRepo = $this->em->getRepository(DiscountItem::class);

            /** @var DiscountItem $discountItem */
            $discountItem = $discountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $discountItemId);

            if ($discountItem === null) {
                throw new DiscountItemNotFoundException();
            }

            $residentDiscountItem = new ResidentDiscountItem();
            $residentDiscountItem->setLedger($ledger);
            $residentDiscountItem->setDiscountItem($discountItem);
            $residentDiscountItem->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);

                if ($ledger->getCreatedAt()->format('Y') !== $date->format('Y') || $ledger->getCreatedAt()->format('m') !== $date->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $residentDiscountItem->setDate($date);
            $residentDiscountItem->setNotes($params['notes']);

            $this->validate($residentDiscountItem, null, ['api_admin_resident_discount_item_add']);

            $this->em->persist($residentDiscountItem);

            //Re-Calculate Ledger Balance Due
            $oldBalanceDue = $ledger->getBalanceDue();
            $newBalanceDue = $oldBalanceDue - $residentDiscountItem->getAmount();
            $ledger->setBalanceDue($newBalanceDue);
            $this->em->persist($ledger);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentDiscountItem->getId();
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

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            /** @var ResidentDiscountItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $id);

            if ($entity === null) {
                throw new ResidentDiscountItemNotFoundException();
            }

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $discountItemId = $params['discount_item_id'] ?? 0;

            /** @var DiscountItemRepository $discountItemRepo */
            $discountItemRepo = $this->em->getRepository(DiscountItem::class);

            /** @var DiscountItem $discountItem */
            $discountItem = $discountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $discountItemId);

            if ($discountItem === null) {
                throw new DiscountItemNotFoundException();
            }

            $entity->setLedger($ledger);
            $entity->setDiscountItem($discountItem);
            $entity->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);

                if ($ledger->getCreatedAt()->format('Y') !== $date->format('Y') || $ledger->getCreatedAt()->format('m') !== $date->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $entity->setDate($date);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_discount_item_edit']);

            $this->em->persist($entity);

            //Re-Calculate Ledger Balance Due
            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();

            $changeSet = $this->em->getUnitOfWork()->getEntityChangeSet($entity);

            if (!empty($changeSet) && array_key_exists('amount', $changeSet)) {
                $oldBalanceDue = $ledger->getBalanceDue();
                $newBalanceDue = $oldBalanceDue - $changeSet['amount']['1'] + $changeSet['amount']['0'];
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

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            /** @var ResidentDiscountItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $id);

            if ($entity === null) {
                throw new ResidentDiscountItemNotFoundException();
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
                throw new ResidentDiscountItemNotFoundException();
            }

            /** @var ResidentDiscountItemRepository $repo */
            $repo = $this->em->getRepository(ResidentDiscountItem::class);

            $residentDiscountItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiscountItem::class), $ids);

            if (empty($residentDiscountItems)) {
                throw new ResidentDiscountItemNotFoundException();
            }

            /**
             * @var ResidentDiscountItem $residentDiscountItem
             */
            foreach ($residentDiscountItems as $residentDiscountItem) {
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
