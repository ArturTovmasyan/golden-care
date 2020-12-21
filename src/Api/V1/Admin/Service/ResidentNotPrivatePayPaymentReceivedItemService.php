<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentPaymentReceivedItemNotFoundException;
use App\Api\V1\Common\Service\Exception\RpPaymentTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ResidentNotPrivatePayPaymentReceivedItem;
use App\Entity\ResidentLedger;
use App\Entity\RpPaymentType;
use App\Repository\ResidentNotPrivatePayPaymentReceivedItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\RpPaymentTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentNotPrivatePayPaymentReceivedItemService
 * @package App\Api\V1\Admin\Service
 */
class ResidentNotPrivatePayPaymentReceivedItemService extends BaseService implements IGridService
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
            ->where('rpri.ledger = :ledgerId')
            ->setParameter('ledgerId', $ledgerId);

        /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $repo */
        $repo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentNotPrivatePayPaymentReceivedItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentNotPrivatePayPaymentReceivedItem::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentNotPrivatePayPaymentReceivedItem|null|object
     */
    public function getById($id)
    {
        /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $repo */
        $repo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentNotPrivatePayPaymentReceivedItem::class), $id);
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

            $paymentTypeId = $params['payment_type_id'] ?? 0;

            /** @var RpPaymentTypeRepository $paymentTypeRepo */
            $paymentTypeRepo = $this->em->getRepository(RpPaymentType::class);

            /** @var RpPaymentType $paymentType */
            $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);

            if ($paymentType === null) {
                throw new RpPaymentTypeNotFoundException();
            }

            $residentPaymentReceivedItem = new ResidentNotPrivatePayPaymentReceivedItem();
            $residentPaymentReceivedItem->setLedger($ledger);
            $residentPaymentReceivedItem->setPaymentType($paymentType);
            $residentPaymentReceivedItem->setAmount($params['amount']);

            $date = null;
            if (!empty($params['date'])) {
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);

                if ($ledger->getCreatedAt()->format('Y') !== $date->format('Y') || $ledger->getCreatedAt()->format('m') !== $date->format('m')) {
                    throw new InvalidEffectiveDateException();
                }
            }

            $residentPaymentReceivedItem->setDate($date);
            $residentPaymentReceivedItem->setTransactionNumber($params['transaction_number']);
            $residentPaymentReceivedItem->setNotes($params['notes']);

            $this->validate($residentPaymentReceivedItem, null, ['api_admin_resident_not_private_pay_payment_received_item_add']);

            $this->em->persist($residentPaymentReceivedItem);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentPaymentReceivedItem->getId();
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

            /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);

            /** @var ResidentNotPrivatePayPaymentReceivedItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentNotPrivatePayPaymentReceivedItem::class), $id);

            if ($entity === null) {
                throw new ResidentPaymentReceivedItemNotFoundException();
            }

            $ledgerId = $params['ledger_id'] ?? 0;

            /** @var ResidentLedgerRepository $residentLedgerRepo */
            $residentLedgerRepo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $ledger */
            $ledger = $residentLedgerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ledgerId);

            if ($ledger === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $paymentTypeId = $params['payment_type_id'] ?? 0;

            /** @var RpPaymentTypeRepository $paymentTypeRepo */
            $paymentTypeRepo = $this->em->getRepository(RpPaymentType::class);

            /** @var RpPaymentType $paymentType */
            $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);

            if ($paymentType === null) {
                throw new RpPaymentTypeNotFoundException();
            }

            $entity->setLedger($ledger);
            $entity->setPaymentType($paymentType);
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
            $entity->setTransactionNumber($params['transaction_number']);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_not_private_pay_payment_received_item_edit']);

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

            /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);

            /** @var ResidentNotPrivatePayPaymentReceivedItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentNotPrivatePayPaymentReceivedItem::class), $id);

            if ($entity === null) {
                throw new ResidentPaymentReceivedItemNotFoundException();
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
                throw new ResidentPaymentReceivedItemNotFoundException();
            }

            /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);

            $residentPaymentReceivedItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentNotPrivatePayPaymentReceivedItem::class), $ids);

            if (empty($residentPaymentReceivedItems)) {
                throw new ResidentPaymentReceivedItemNotFoundException();
            }

            /**
             * @var ResidentNotPrivatePayPaymentReceivedItem $residentPaymentReceivedItem
             */
            foreach ($residentPaymentReceivedItems as $residentPaymentReceivedItem) {
                $this->em->remove($residentPaymentReceivedItem);
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
            throw new ResidentPaymentReceivedItemNotFoundException();
        }

        /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $repo */
        $repo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentNotPrivatePayPaymentReceivedItem::class), $ids);

        if (empty($entities)) {
            throw new ResidentPaymentReceivedItemNotFoundException();
        }

        return $this->getRelatedData(ResidentNotPrivatePayPaymentReceivedItem::class, $entities);
    }
}
