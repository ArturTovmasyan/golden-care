<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentPaymentReceivedItemNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentRentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\Exception\RpPaymentTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ResidentPrivatePayPaymentReceivedItem;
use App\Entity\ResidentLedger;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\RpPaymentType;
use App\Repository\ResidentPrivatePayPaymentReceivedItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Repository\RpPaymentTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentPrivatePayPaymentReceivedItemService
 * @package App\Api\V1\Admin\Service
 */
class ResidentPrivatePayPaymentReceivedItemService extends BaseService implements IGridService
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

        /** @var ResidentPrivatePayPaymentReceivedItemRepository $repo */
        $repo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPrivatePayPaymentReceivedItem::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['ledger_id'])) {
            $ledgerId = $params[0]['ledger_id'];

            /** @var ResidentPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPrivatePayPaymentReceivedItem::class), $ledgerId);
        }

        throw new ResidentLedgerNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentPrivatePayPaymentReceivedItem|null|object
     */
    public function getById($id)
    {
        /** @var ResidentPrivatePayPaymentReceivedItemRepository $repo */
        $repo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPrivatePayPaymentReceivedItem::class), $id);
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

            $rentId = $params['rent_id'] ?? 0;

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository( ResidentRent::class);

            /** @var ResidentRent $rent */
            $rent = $rentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $rentId);

            if ($rent === null) {
                throw new ResidentRentNotFoundException();
            }

            $responsiblePersonId = $params['responsible_person_id'] ?? 0;

            /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository( ResidentResponsiblePerson::class);

            /** @var ResidentResponsiblePerson $responsiblePerson */
            $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $responsiblePersonId);

            if ($responsiblePerson === null) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            $residentPaymentReceivedItem = new ResidentPrivatePayPaymentReceivedItem();
            $residentPaymentReceivedItem->setLedger($ledger);
            $residentPaymentReceivedItem->setPaymentType($paymentType);
            $residentPaymentReceivedItem->setRent($rent);
            $residentPaymentReceivedItem->setResponsiblePerson($responsiblePerson);
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

            $this->validate($residentPaymentReceivedItem, null, ['api_admin_resident_private_pay_payment_received_item_add']);

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

            /** @var ResidentPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);

            /** @var ResidentPrivatePayPaymentReceivedItem $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPrivatePayPaymentReceivedItem::class), $id);

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

            $rentId = $params['rent_id'] ?? 0;

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository( ResidentRent::class);

            /** @var ResidentRent $rent */
            $rent = $rentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $rentId);

            if ($rent === null) {
                throw new ResidentRentNotFoundException();
            }

            $responsiblePersonId = $params['responsible_person_id'] ?? 0;

            /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository( ResidentResponsiblePerson::class);

            /** @var ResidentResponsiblePerson $responsiblePerson */
            $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $responsiblePersonId);

            if ($responsiblePerson === null) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            $entity->setLedger($ledger);
            $entity->setPaymentType($paymentType);
            $entity->setRent($rent);
            $entity->setResponsiblePerson($responsiblePerson);
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

            $this->validate($entity, null, ['api_admin_resident_private_pay_payment_received_item_edit']);

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

            /** @var ResidentPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);

            /** @var ResidentPrivatePayPaymentReceivedItem $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPrivatePayPaymentReceivedItem::class), $id);

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

            /** @var ResidentPrivatePayPaymentReceivedItemRepository $repo */
            $repo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);

            $residentPaymentReceivedItems = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPrivatePayPaymentReceivedItem::class), $ids);

            if (empty($residentPaymentReceivedItems)) {
                throw new ResidentPaymentReceivedItemNotFoundException();
            }

            /**
             * @var ResidentPrivatePayPaymentReceivedItem $residentPaymentReceivedItem
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

        /** @var ResidentPrivatePayPaymentReceivedItemRepository $repo */
        $repo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentPrivatePayPaymentReceivedItem::class), $ids);

        if (empty($entities)) {
            throw new ResidentPaymentReceivedItemNotFoundException();
        }

        return $this->getRelatedData(ResidentPrivatePayPaymentReceivedItem::class, $entities);
    }
}
