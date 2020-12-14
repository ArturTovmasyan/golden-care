<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CreditItemNotFoundException;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentCreditItemNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CreditItem;
use App\Entity\ResidentCreditItem;
use App\Entity\ResidentLedger;
use App\Repository\CreditItemRepository;
use App\Repository\ResidentCreditItemRepository;
use App\Repository\ResidentLedgerRepository;
use Doctrine\ORM\QueryBuilder;

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
        if (empty($params) || empty($params[0]['ledger_id'])) {
            throw new ResidentLedgerNotFoundException();
        }

        $ledgerId = $params[0]['ledger_id'];

        $queryBuilder
            ->where('rci.ledger = :ledgerId')
            ->setParameter('ledgerId', $ledgerId);

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
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
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

            $creditItemId = $params['credit_item_id'] ?? 0;

            /** @var CreditItemRepository $creditItemRepo */
            $creditItemRepo = $this->em->getRepository(CreditItem::class);

            /** @var CreditItem $creditItem */
            $creditItem = $creditItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $creditItemId);

            if ($creditItem === null) {
                throw new CreditItemNotFoundException();
            }

            $residentCreditItem = new ResidentCreditItem();
            $residentCreditItem->setLedger($ledger);
            $residentCreditItem->setCreditItem($creditItem);
            $residentCreditItem->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);

                if ($ledger->getCreatedAt()->format('Y') !== $date->format('Y') || $ledger->getCreatedAt()->format('m') !== $date->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $residentCreditItem->setDate($date);
            $residentCreditItem->setNotes($params['notes']);

            $this->validate($residentCreditItem, null, ['api_admin_resident_credit_item_add']);

            $this->em->persist($residentCreditItem);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentCreditItem->getId();
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

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            /** @var ResidentCreditItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $id);

            if ($entity === null) {
                throw new ResidentCreditItemNotFoundException();
            }

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $creditItemId = $params['credit_item_id'] ?? 0;

            /** @var CreditItemRepository $creditItemRepo */
            $creditItemRepo = $this->em->getRepository(CreditItem::class);

            /** @var CreditItem $creditItem */
            $creditItem = $creditItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $creditItemId);

            if ($creditItem === null) {
                throw new CreditItemNotFoundException();
            }

            $entity->setLedger($ledger);
            $entity->setCreditItem($creditItem);
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

            $this->validate($entity, null, ['api_admin_resident_credit_item_edit']);

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

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            /** @var ResidentCreditItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $id);

            if ($entity === null) {
                throw new ResidentCreditItemNotFoundException();
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
                throw new ResidentCreditItemNotFoundException();
            }

            /** @var ResidentCreditItemRepository $repo */
            $repo = $this->em->getRepository(ResidentCreditItem::class);

            $residentCreditItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentCreditItem::class), $ids);

            if (empty($residentCreditItems)) {
                throw new ResidentCreditItemNotFoundException();
            }

            /**
             * @var ResidentCreditItem $residentCreditItem
             */
            foreach ($residentCreditItems as $residentCreditItem) {
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
