<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\KeyFinanceTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerAlreadyExistException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\ResidentCreditDiscountItem;
use App\Entity\ResidentExpenseItem;
use App\Entity\ResidentPaymentReceivedItem;
use App\Entity\ResidentRent;
use App\Model\KeyFinanceType as KeyFinanceCategory;
use App\Entity\KeyFinanceType;
use App\Entity\Resident;
use App\Entity\ResidentKeyFinanceDate;
use App\Entity\ResidentLedger;
use App\Model\RentPeriod;
use App\Repository\KeyFinanceTypeRepository;
use App\Repository\ResidentCreditDiscountItemRepository;
use App\Repository\ResidentExpenseItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentPaymentReceivedItemRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\QueryBuilder;
use function Matrix\trace;

/**
 * Class ResidentLedgerService
 * @package App\Api\V1\Admin\Service
 */
class ResidentLedgerService extends BaseService implements IGridService
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
            ->where('rl.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentLedgerRepository $repo */
        $repo = $this->em->getRepository(ResidentLedger::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentLedgerRepository $repo */
            $repo = $this->em->getRepository(ResidentLedger::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @param $gridData
     * @return ResidentLedger
     */
    public function getById($id, $gridData)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentLedgerRepository $repo */
        $repo = $this->em->getRepository(ResidentLedger::class);
        /** @var ResidentLedger $entity */
        $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $id);

        $this->setPreviousAndNextItemIdsFromGrid($entity, $gridData);

        return $entity;
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
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

            $residentLedger = new ResidentLedger();
            $residentLedger->setResident($resident);


            $now = new \DateTime('now');
            /** @var ResidentLedgerRepository $repo */
            $repo = $this->em->getRepository(ResidentLedger::class);
            $existingLedger = $repo->getAddedYearAndMonthLedger($currentSpace, null, $residentId, $now);

            if ($existingLedger !== null) {
                throw new ResidentLedgerAlreadyExistException();
            }

            //Calculate Amount
            $amount = $this->calculateAmount($currentSpace, $residentId, $now);

            $residentLedger->setAmount(round($amount, 2));

            //Calculate Balance Due
            $currentMonthBalanceDue = $amount;

            //Calculate Previous Month Balance Due
            $previousDate = new \DateTime(date('Y-m-d', strtotime($now->format('Y-m-d')." first day of previous month")));
            $dateStartFormatted = $previousDate->format('m/01/Y 00:00:00');
            $dateEndFormatted = $previousDate->format('m/t/Y 23:59:59');
            $dateStart = new \DateTime($dateStartFormatted);
            $dateEnd = new \DateTime($dateEndFormatted);

            /** @var ResidentLedger $previousLedger */
            $previousLedger = $repo->getPreviousLedger($currentSpace, null, $residentId, $dateStart, $dateEnd);

            $previousMonthBalanceDue = 0;
            if ($previousLedger === null) {
                //Calculate Previous Month Amount
                $previousMonthAmount = $this->calculateAmount($currentSpace, $residentId, $previousDate);

                $previousMonthBalanceDue = $previousMonthAmount;
            }

            $residentLedger->setBalanceDue(round($currentMonthBalanceDue + $previousMonthBalanceDue, 2));

            $this->validate($residentLedger, null, ['api_admin_resident_ledger_add']);

            $this->em->persist($residentLedger);

            //Add "Monthly Billing Cut Off Date" Key Finance Date
            $residentKeyFinanceDate = new ResidentKeyFinanceDate();
            $residentKeyFinanceDate->setLedger($residentLedger);

            /** @var KeyFinanceTypeRepository $keyFinanceTypeRepo */
            $keyFinanceTypeRepo = $this->em->getRepository(KeyFinanceType::class);

            /** @var KeyFinanceType $keyFinanceType */
            $keyFinanceType = $keyFinanceTypeRepo->findOneBy(['space' => $currentSpace, 'type' => KeyFinanceCategory::MONTHLY_BILLING_CUT_OFF_DATE]);

            if ($keyFinanceType === null) {
                throw new KeyFinanceTypeNotFoundException();
            }

            $residentKeyFinanceDate->setKeyFinanceType($keyFinanceType);

            $now = new \DateTime('now');
            $dateString = $now->format('Y-m') . '-18';
            $date = new \DateTime($dateString);

            $residentKeyFinanceDate->setDate($date);

            $this->validate($residentKeyFinanceDate, null, ['api_admin_resident_key_finance_date_add']);

            $this->em->persist($residentKeyFinanceDate);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentLedger->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $currentSpace
     * @param $residentId
     * @param $now
     * @return int|mixed
     * @throws \Exception
     */
    public function calculateAmount($currentSpace, $residentId, $now)
    {
        $dateStartFormatted = $now->format('m/01/Y 00:00:00');
        $dateEndFormatted = $now->format('m/t/Y 23:59:59');

        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);
        $diff = $dateEnd->diff($dateStart)->days + 1;

        $subInterval = ImtDateTimeInterval::getWithDateTimes($dateStart, $dateEnd);

        /** @var ResidentRentRepository $residentRentRepo */
        $residentRentRepo = $this->em->getRepository(ResidentRent::class);
        $data = $residentRentRepo->getAdmissionRoomRentDataForLedgerAmount($currentSpace, null, $subInterval, $residentId);
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);

        $amount = 0;
        if (!empty($data)) {
            foreach ($data as $rent) {
                $discharged = $rent['discharged'] !== null ? new \DateTime($rent['discharged']) : $dateEnd;
                $discharged->setTime(23,59,59);
                $admitted = new \DateTime($rent['admitted']);
                $admitted->setTime(0,0,0);
                $admitted = $admitted < $dateStart ? $dateStart : $admitted;

                $rentDiff = $discharged->diff($admitted)->days + 1;

                if ($rentDiff >= $diff) {
                    $calculationResults = [
                        'days' => $diff,
                        'amount' => $rent['amount'],
                    ];
                } else {
                    $calculationResults = $rentPeriodFactory->calculateForRoomRentInterval(
                        ImtDateTimeInterval::getWithDateTimes($admitted, $discharged),
                        RentPeriod::MONTHLY,
                        $rent['amount']
                    );
                }

                $amount += $calculationResults['amount'];
            }
        }

        return $amount;
    }

    /**
     * @param $currentSpace
     * @param $ledgerId
     * @param $now
     * @return int|mixed
     * @throws \Exception
     */
    public function calculateRelationsAmount($currentSpace, $ledgerId, $now)
    {
        $dateStartFormatted = $now->format('m/01/Y 00:00:00');
        $dateEndFormatted = $now->format('m/t/Y 23:59:59');

        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);

        /** @var ResidentExpenseItemRepository $residentExpenseItemRepo */
        $residentExpenseItemRepo = $this->em->getRepository(ResidentExpenseItem::class);
        $residentExpenseItems = $residentExpenseItemRepo->getByInterval($currentSpace, null, $ledgerId, $dateStart, $dateEnd);

        $expenseItemAmount = 0;
        if (!empty($residentExpenseItems)) {
            foreach ($residentExpenseItems as $expenseItem) {
                $expenseItemAmount += $expenseItem['amount'];
            }
        }

        /** @var ResidentCreditDiscountItemRepository $residentCreditDiscountItemRepo */
        $residentCreditDiscountItemRepo = $this->em->getRepository(ResidentCreditDiscountItem::class);
        $residentCreditDiscountItems = $residentCreditDiscountItemRepo->getByInterval($currentSpace, null, $ledgerId, $dateStart, $dateEnd);

        $creditDiscountItemAmount = 0;
        if (!empty($residentCreditDiscountItems)) {
            foreach ($residentCreditDiscountItems as $creditDiscountItem) {
                $creditDiscountItemAmount += $creditDiscountItem['amount'];
            }
        }

        /** @var ResidentPaymentReceivedItemRepository $residentPaymentReceivedItemRepo */
        $residentPaymentReceivedItemRepo = $this->em->getRepository(ResidentPaymentReceivedItem::class);
        $residentPaymentReceivedItems = $residentPaymentReceivedItemRepo->getByInterval($currentSpace, null, $ledgerId, $dateStart, $dateEnd);

        $paymentReceivedItemAmount = 0;
        if (!empty($residentPaymentReceivedItems)) {
            foreach ($residentPaymentReceivedItems as $paymentReceivedItem) {
                $paymentReceivedItemAmount += $paymentReceivedItem['amount'];
            }
        }

        return $expenseItemAmount + $creditDiscountItemAmount + $paymentReceivedItemAmount;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentLedgerRepository $repo */
            $repo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $id);

            if ($entity === null) {
                throw new ResidentLedgerNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $entity->setResident($resident);

            //Calculate amount
            $now = $entity->getCreatedAt() ?? new \DateTime('now');
            $amount = $this->calculateAmount($currentSpace, $residentId, $now);

            $entity->setAmount(round($amount, 2));

            //Calculate Balance Due
            $relationsAmount = $this->calculateRelationsAmount($currentSpace, $entity->getId(), $now);
            $currentMonthBalanceDue = $amount + $relationsAmount;

            //Calculate Previous Month Balance Due
            $previousDate = new \DateTime(date('Y-m-d', strtotime($now->format('Y-m-d')." first day of previous month")));
            $dateStartFormatted = $previousDate->format('m/01/Y 00:00:00');
            $dateEndFormatted = $previousDate->format('m/t/Y 23:59:59');
            $dateStart = new \DateTime($dateStartFormatted);
            $dateEnd = new \DateTime($dateEndFormatted);

            /** @var ResidentLedger $previousLedger */
            $previousLedger = $repo->getPreviousLedger($currentSpace, null, $residentId, $dateStart, $dateEnd);

            $previousMonthBalanceDue = 0;
            if ($previousLedger === null) {
                //Calculate Previous Month Amount
                $previousMonthAmount = $this->calculateAmount($currentSpace, $residentId, $previousDate);
                $previousMonthRelationsAmount = $this->calculateRelationsAmount($currentSpace, $entity->getId(), $previousDate);

                $previousMonthBalanceDue = $previousMonthAmount + $previousMonthRelationsAmount;
            }

            $entity->setBalanceDue(round($currentMonthBalanceDue + $previousMonthBalanceDue, 2));

            $this->validate($entity, null, ['api_admin_resident_ledger_edit']);

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

            /** @var ResidentLedgerRepository $repo */
            $repo = $this->em->getRepository(ResidentLedger::class);

            /** @var ResidentLedger $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $id);

            if ($entity === null) {
                throw new ResidentLedgerNotFoundException();
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
                throw new ResidentLedgerNotFoundException();
            }

            /** @var ResidentLedgerRepository $repo */
            $repo = $this->em->getRepository(ResidentLedger::class);

            $residentLedgers = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ids);

            if (empty($residentLedgers)) {
                throw new ResidentLedgerNotFoundException();
            }

            /**
             * @var ResidentLedger $residentLedger
             */
            foreach ($residentLedgers as $residentLedger) {
                $this->em->remove($residentLedger);
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
            throw new ResidentLedgerNotFoundException();
        }

        /** @var ResidentLedgerRepository $repo */
        $repo = $this->em->getRepository(ResidentLedger::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $ids);

        if (empty($entities)) {
            throw new ResidentLedgerNotFoundException();
        }

        return $this->getRelatedData(ResidentLedger::class, $entities);
    }
}
