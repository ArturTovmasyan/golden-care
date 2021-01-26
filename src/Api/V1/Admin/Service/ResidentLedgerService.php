<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\LatePaymentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerAlreadyExistException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\LatePayment;
use App\Entity\PaymentSource;
use App\Entity\ResidentAwayDays;
use App\Entity\ResidentCreditItem;
use App\Entity\ResidentDiscountItem;
use App\Entity\ResidentExpenseItem;
use App\Entity\ResidentNotPrivatePayPaymentReceivedItem;
use App\Entity\ResidentPrivatePayPaymentReceivedItem;
use App\Entity\ResidentRent;
use App\Entity\Resident;
use App\Entity\ResidentLedger;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\RpPaymentType;
use App\Model\RentPeriod;
use App\Repository\LatePaymentRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\ResidentAwayDaysRepository;
use App\Repository\ResidentCreditItemRepository;
use App\Repository\ResidentDiscountItemRepository;
use App\Repository\ResidentExpenseItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentNotPrivatePayPaymentReceivedItemRepository;
use App\Repository\ResidentPrivatePayPaymentReceivedItemRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Repository\RpPaymentTypeRepository;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\QueryBuilder;

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

        $currentSpace = $this->grantService->getCurrentSpace();

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rl.resident = :residentId')
            ->setParameter('residentId', $residentId);

        if (!empty($params[0]['rent_id'])) {
            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);
            /** @var ResidentRent $rent */
            $rent = $rentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $params[0]['rent_id']);

            if ($rent !== null) {
                $queryBuilder
                    ->andWhere('rl.createdAt >= :start')
                    ->setParameter('start', $rent->getStart());

                if ($rent->getEnd() !== null) {
                    $queryBuilder
                        ->andWhere('rl.createdAt <= :end')
                        ->setParameter('end', $rent->getEnd());
                }
            }
        }

        /** @var ResidentLedgerRepository $repo */
        $repo = $this->em->getRepository(ResidentLedger::class);

        $repo->search($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $queryBuilder);
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getById($id, $gridData)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentLedgerRepository $repo */
        $repo = $this->em->getRepository(ResidentLedger::class);
        /** @var ResidentLedger $entity */
        $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $id);

        if ($entity !== null && $entity->getResident() !== null) {
            $priorLedgerData = $this->calculatePriorLedgerData($currentSpace, $repo, $entity->getResident()->getId(), $entity->getCreatedAt());
            $entity->setPriorPrivatePayBalanceDue($priorLedgerData['priorPrivatPayBalanceDue']);
            $entity->setPriorNotPrivatePayBalanceDue($priorLedgerData['priorNotPrivatPayBalanceDue']);

            $this->setPreviousAndNextItemIdsFromGrid($entity, $gridData);
        }

        return $entity;
    }

    /**
     * @param $currentSpace
     * @param ResidentLedgerRepository $repo
     * @param $residentId
     * @param $calculationDate
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function calculatePriorLedgerData($currentSpace, ResidentLedgerRepository $repo, $residentId, $calculationDate): array
    {
        $priorPrivatPayBalanceDue = 0;
        $priorNotPrivatPayBalanceDue = 0;

        //Calculate Prior Months Balance Due
        $previousDate = new \DateTime(date('Y-m-d', strtotime($calculationDate->format('Y-m-d')." first day of previous month")));
        $priorDateFormatted = $previousDate->format('m/t/Y 23:59:59');
        $priorDate = new \DateTime($priorDateFormatted);

        $priorLedgers = $repo->getResidentPriorLedgersBalanceDue($currentSpace, null, $residentId, $priorDate);

        if (!empty($priorLedgers)) {
            foreach ($priorLedgers as $priorLedger) {
                $priorPrivatPayBalanceDue += $priorLedger['privatePayBalanceDue'];
                $priorNotPrivatPayBalanceDue += $priorLedger['notPrivatePayBalanceDue'];
            }
        }

        return [
            'priorPrivatPayBalanceDue' => round($priorPrivatPayBalanceDue, 2),
            'priorNotPrivatPayBalanceDue' => round($priorNotPrivatPayBalanceDue, 2),
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function getRents($id): array
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentLedgerRepository $repo */
        $repo = $this->em->getRepository(ResidentLedger::class);
        /** @var ResidentLedger $entity */
        $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentLedger::class), $id);

        $rentData = [];
        if ($entity !== null && $entity->getResident() !== null) {
            $amountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $entity->getResident()->getId(), $entity->getCreatedAt(), null);
            $rentData = $amountData['rentData'];
        }

        return $rentData;
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

            if (!empty($params['late_payment_id'])) {
                /** @var LatePaymentRepository $latePaymentRepo */
                $latePaymentRepo = $this->em->getRepository(LatePayment::class);

                /** @var LatePayment $latePayment */
                $latePayment = $latePaymentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $params['late_payment_id']);

                if ($latePayment === null) {
                    throw new LatePaymentNotFoundException();
                }

                $residentLedger->setLatePayment($latePayment);
            } else {
                $residentLedger->setLatePayment(null);
            }

            $now = new \DateTime('now');
            /** @var ResidentLedgerRepository $repo */
            $repo = $this->em->getRepository(ResidentLedger::class);
            $existingLedger = $repo->getAddedYearAndMonthLedger($currentSpace, null, $residentId, $now);

            if ($existingLedger !== null) {
                throw new ResidentLedgerAlreadyExistException();
            }

            $this->validate($residentLedger, null, ['api_admin_resident_ledger_add']);

            $this->em->persist($residentLedger);
            $this->em->flush();

            $now = $residentLedger->getCreatedAt() ?? new \DateTime('now');

            $awayDays = [];
            $dateStartFormatted = $now->format('m/01/Y 00:00:00');
            $dateEndFormatted = $now->format('m/t/Y 23:59:59');

            $dateStart = new \DateTime($dateStartFormatted);
            $dateEnd = new \DateTime($dateEndFormatted);

            /** @var ResidentAwayDaysRepository $residentAwayDaysRepo */
            $residentAwayDaysRepo = $this->em->getRepository(ResidentAwayDays::class);
            $residentAwayDays = $residentAwayDaysRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);
            if (!empty($residentAwayDays)) {
                /** @var ResidentAwayDays $residentAwayDay */
                foreach ($residentAwayDays as $residentAwayDay) {
                    $awayDays[] = ImtDateTimeInterval::getWithDateTimes($residentAwayDay->getStart(), $residentAwayDay->getEnd());
                }
            }

            /** @var ResidentExpenseItemRepository $residentExpenseItemRepo */
            $residentExpenseItemRepo = $this->em->getRepository(ResidentExpenseItem::class);
            $residentExpenseItems = $residentExpenseItemRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);

            $expenseItemAmount = 0;
            if (!empty($residentExpenseItems)) {
                foreach ($residentExpenseItems as $expenseItem) {
                    $expenseItemAmount += $expenseItem['amount'];
                }
            }

            /** @var ResidentCreditItemRepository $residentCreditItemRepo */
            $residentCreditItemRepo = $this->em->getRepository(ResidentCreditItem::class);
            $residentCreditItems = $residentCreditItemRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);

            $creditItemAmount = 0;
            if (!empty($residentCreditItems)) {
                foreach ($residentCreditItems as $creditItem) {
                    $creditItemAmount += $creditItem['amount'];
                }
            }

            /** @var ResidentDiscountItemRepository $residentDiscountItemRepo */
            $residentDiscountItemRepo = $this->em->getRepository(ResidentDiscountItem::class);
            $residentDiscountItems = $residentDiscountItemRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);

            $discountItemAmount = 0;
            if (!empty($residentDiscountItems)) {
                foreach ($residentDiscountItems as $discountItem) {
                    $discountItemAmount += $discountItem['amount'];
                }
            }

            $amountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $now, $awayDays);
            //Calculate Privat Pay Balance Due
            $currentMonthPrivatPayBalanceDue = $amountData['privatPayAmount'] + $expenseItemAmount - $creditItemAmount - $discountItemAmount;
            //Calculate Not Privat Pay Balance Due
            $currentMonthNotPrivatPayBalanceDue = $amountData['notPrivatPayAmount'];

            $residentLedger->setPrivatePayBalanceDue(round($currentMonthPrivatPayBalanceDue, 2));
            $residentLedger->setNotPrivatePayBalanceDue(round($currentMonthNotPrivatPayBalanceDue, 2));

            $residentLedger->setSource($amountData['paymentSources']);
            $residentLedger->setPrivatPaySource($amountData['privatPayPaymentSources']);
            $residentLedger->setNotPrivatPaySource($amountData['notPrivatPayPaymentSources']);
            $residentLedger->setAwayDays($amountData['awayDays']);

            $this->em->persist($residentLedger);
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
     * @param array|null $awayDays
     * @return array
     * @throws \Exception
     */
    public function calculateAmountAndGetPaymentSources($currentSpace, $residentId, $now, array $awayDays = null)
    {
        /** @var PaymentSourceRepository $paymentSourceRepo */
        $paymentSourceRepo = $this->em->getRepository(PaymentSource::class);

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
        $sourceAmount = 0;
        $privatPaySourceAmount = 0;
        $notPrivatPaySourceAmount = 0;
        $paymentSources = [];
        $privatPayPaymentSources = [];
        $finalPrivatPayPaymentSources = [];
        $notPrivatPayPaymentSources = [];
        $finalNotPrivatPayPaymentSources = [];
        $rentData = [];
        $residentAwayDays = [];
        $finalAwayDays = [];
        if (!empty($data)) {
            foreach ($data as $rent) {
                $rentData[] = [
                    'id' => $rent['rentId'],
                    'resident' => $rent['firstName'] . ' ' . $rent['lastName'],
                    'start' => $rent['start'],
                    'amount' => $rent['amount'],
                    'reason' => $rent['reason'] ?? '',
                    'sources' => $rent['sources'],
                    'room' => $rent['room'],
                    'roomType' => $rent['roomType'],
                    'baseRate' => $rent['baseRate'] ?? 0,
                    'typeShorthand' => $rent['typeShorthand'] ?? 0,
                ];

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

                if (!empty($rent['sources'])) {
                    $sourceIds = array_column($rent['sources'], 'id');
                    $sources = $paymentSourceRepo->findByIds($currentSpace, null, $sourceIds);

                    $periods = [];
                    $privatePayIds = [];
                    $sourceAwayDaysOnIds = [];
                    $sourceAdditionalFields = [];
                    /** @var PaymentSource $source */
                    foreach ($sources as $source) {
                        $periods[$source->getId()] = $source->getPeriod();

                        if ($source->isPrivatePay()) {
                            $privatePayIds[$source->getId()] = $source->getId();
                        }

                        if ($source->isOnlyForOccupiedDays()) {
                            $sourceAwayDaysOnIds[$source->getId()] = $source->getId();
                        }

                        if (!empty($source->getFieldName())) {
                            $sourceAdditionalFields[$source->getId()] = $source->getFieldName();
                        }
                    }

                    foreach ($rent['sources'] as $rentSource) {
                        if (in_array($rentSource['id'], $privatePayIds, false)) {
                            $calcResults = $rentPeriodFactory->calculateForRoomRentInterval(
                                ImtDateTimeInterval::getWithDateTimes($admitted, $discharged),
                                $periods[$rentSource['id']],
                                $rentSource['amount']
                            );

                            $privatPaySourceAmount += $calcResults['amount'];

                            $rentSourceId = $rentSource['id'];
                            $privatePayArray = [
                                'id' => $rentSource['id'],
                                'amount' => array_key_exists($rentSourceId, $privatPayPaymentSources) ? round($privatPayPaymentSources[$rentSourceId]['amount'] + $calcResults['amount'], 2) : round($calcResults['amount'], 2),
                                'rent_id' => $rent['rentId'],
                                'responsible_person_id' => array_key_exists('responsible_person_id', $rentSource) ? $rentSource['responsible_person_id'] : '',
                                'days' => array_key_exists($rentSourceId, $privatPayPaymentSources) ? $privatPayPaymentSources[$rentSourceId]['days'] + $calcResults['days'] : $calcResults['days'],
                                'field_text' => array_key_exists('field_text', $rentSource) && array_key_exists($rentSource['id'], $sourceAdditionalFields) ? $sourceAdditionalFields[$rentSource['id']] . ' - ' . $rentSource['field_text'] : '',
                            ];
                            if (array_key_exists($rentSourceId, $privatPayPaymentSources)) {
                                unset($privatPayPaymentSources[$rentSourceId]);
                            }
                            $privatPayPaymentSources[$rentSourceId] = $privatePayArray;
                        } else {
                            $calcResults = $rentPeriodFactory->calculateForRoomRentInterval(
                                ImtDateTimeInterval::getWithDateTimes($admitted, $discharged),
                                $periods[$rentSource['id']],
                                $rentSource['amount'],
                                in_array($rentSource['id'], $sourceAwayDaysOnIds, false) ? $awayDays : null
                            );

                            $notPrivatPaySourceAmount += $calcResults['amount'];

                            $rentSourceId = $rentSource['id'];
                            $notPrivatePayArray = [
                                'id' => $rentSource['id'],
                                'amount' => array_key_exists($rentSourceId, $notPrivatPayPaymentSources) ? round($notPrivatPayPaymentSources[$rentSourceId]['amount'] + $calcResults['amount'], 2) : round($calcResults['amount'], 2),
                                'rent_id' => $rent['rentId'],
                                'days' => array_key_exists($rentSourceId, $notPrivatPayPaymentSources) ? $notPrivatPayPaymentSources[$rentSourceId]['days'] + $calcResults['days'] : $calcResults['days'],
                                'absent_days' => array_key_exists($rentSourceId, $notPrivatPayPaymentSources) ? $notPrivatPayPaymentSources[$rentSourceId]['absent_days'] + $calcResults['absentDays'] : $calcResults['absentDays'],
                                'field_text' => array_key_exists('field_text', $rentSource) && array_key_exists($rentSource['id'], $sourceAdditionalFields) ? $sourceAdditionalFields[$rentSource['id']] . ' - ' . $rentSource['field_text'] : '',
                            ];
                            if (array_key_exists($rentSourceId, $privatPayPaymentSources)) {
                                unset($notPrivatPayPaymentSources[$rentSourceId]);
                            }
                            $notPrivatPayPaymentSources[$rentSourceId] = $notPrivatePayArray;

                            if (!empty($calcResults['residentAwayDays'])) {
                                $residentAwayDays[] = $calcResults['residentAwayDays'];
                            }
                        }

                        $sourceAmount += $calcResults['amount'];

                        $paymentSources[] = $rentSource;
                    }

                    $finalPrivatPayPaymentSources = array_values($privatPayPaymentSources);
                    $finalNotPrivatPayPaymentSources = array_values($notPrivatPayPaymentSources);
                }
            }

            if (!empty($residentAwayDays)) {
                $awayDaysData = [];
                foreach ($residentAwayDays as $awayDayArray) {
                    foreach ($awayDayArray as $residentAwayDay) {
                        if (array_key_exists($residentAwayDay['start'], $finalAwayDays)) {
                            $awayDaysData[$residentAwayDay['start']] += $residentAwayDay['days'];
                        } else {
                            $awayDaysData[$residentAwayDay['start']] =  $residentAwayDay['days'];
                        }
                    }
                }

                $awayDaysData = array_combine(array_keys($awayDaysData), array_values($awayDaysData));
                ksort($awayDaysData);

                foreach ($awayDaysData as $key => $value) {
                    $finalAwayDays[] = [
                        'date' => $key,
                        'days' => $value,
                    ];
                }
            }

            $rentData = array_unique($rentData, SORT_REGULAR);
            $tempArr = array_unique(array_column($rentData, 'id'));
            $rentData = array_intersect_key($rentData, $tempArr);
        }

        return [
            'rentData' => $rentData,
            'amount' => $amount,
            'sourceAmount' => $sourceAmount,
            'paymentSources' => $paymentSources,
            'privatPayAmount' => $privatPaySourceAmount,
            'privatPayPaymentSources' => $finalPrivatPayPaymentSources,
            'notPrivatPayAmount' => $notPrivatPaySourceAmount,
            'notPrivatPayPaymentSources' => $finalNotPrivatPayPaymentSources,
            'awayDays' => $finalAwayDays,
        ];
    }

    /**
     * @param $currentSpace
     * @param $ledgerId
     * @param $residentId
     * @param $now
     * @return array
     * @throws \Exception
     */
    public function calculateRelationsAmount($currentSpace, $ledgerId, $residentId, $now): array
    {
        $dateStartFormatted = $now->format('m/01/Y 00:00:00');
        $dateEndFormatted = $now->format('m/t/Y 23:59:59');

        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);

        /** @var ResidentExpenseItemRepository $residentExpenseItemRepo */
        $residentExpenseItemRepo = $this->em->getRepository(ResidentExpenseItem::class);
        $residentExpenseItems = $residentExpenseItemRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);

        $expenseItemAmount = 0;
        if (!empty($residentExpenseItems)) {
            foreach ($residentExpenseItems as $expenseItem) {
                $expenseItemAmount += $expenseItem['amount'];
            }
        }

        /** @var ResidentCreditItemRepository $residentCreditItemRepo */
        $residentCreditItemRepo = $this->em->getRepository(ResidentCreditItem::class);
        $residentCreditItems = $residentCreditItemRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);

        $creditItemAmount = 0;
        if (!empty($residentCreditItems)) {
            foreach ($residentCreditItems as $creditItem) {
                $creditItemAmount += $creditItem['amount'];
            }
        }

        /** @var ResidentDiscountItemRepository $residentDiscountItemRepo */
        $residentDiscountItemRepo = $this->em->getRepository(ResidentDiscountItem::class);
        $residentDiscountItems = $residentDiscountItemRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);

        $discountItemAmount = 0;
        if (!empty($residentDiscountItems)) {
            foreach ($residentDiscountItems as $residentDiscountItem) {
                $discountItemAmount += $residentDiscountItem['amount'];
            }
        }

        /** @var ResidentPrivatePayPaymentReceivedItemRepository $residentPrivatePayPaymentReceivedItemRepo */
        $residentPrivatePayPaymentReceivedItemRepo = $this->em->getRepository(ResidentPrivatePayPaymentReceivedItem::class);
        $residentPrivatePayPaymentReceivedItems = $residentPrivatePayPaymentReceivedItemRepo->getByInterval($currentSpace, null, $ledgerId, $dateStart, $dateEnd);

        $privatePayPaymentReceivedItemAmount = 0;
        if (!empty($residentPrivatePayPaymentReceivedItems)) {
            foreach ($residentPrivatePayPaymentReceivedItems as $privatePayPaymentReceivedItem) {
                $privatePayPaymentReceivedItemAmount += $privatePayPaymentReceivedItem['amount'];
            }
        }

        /** @var ResidentNotPrivatePayPaymentReceivedItemRepository $residentNotPrivatePayPaymentReceivedItemRepo */
        $residentNotPrivatePayPaymentReceivedItemRepo = $this->em->getRepository(ResidentNotPrivatePayPaymentReceivedItem::class);
        $residentNotPrivatePayPaymentReceivedItems = $residentNotPrivatePayPaymentReceivedItemRepo->getByInterval($currentSpace, null, $ledgerId, $dateStart, $dateEnd);

        $notPrivatePayPaymentReceivedItemAmount = 0;
        if (!empty($residentNotPrivatePayPaymentReceivedItems)) {
            foreach ($residentNotPrivatePayPaymentReceivedItems as $notPrivatePayPaymentReceivedItem) {
                $notPrivatePayPaymentReceivedItemAmount += $notPrivatePayPaymentReceivedItem['amount'];
            }
        }

        $privatePayRelationsAmount = $expenseItemAmount - $creditItemAmount - $discountItemAmount - $privatePayPaymentReceivedItemAmount;
        $notPrivatePayRelationsAmount = -$notPrivatePayPaymentReceivedItemAmount;

        return [
            'privatePayRelationsAmount' => $privatePayRelationsAmount,
            'notPrivatePayRelationsAmount' => $notPrivatePayRelationsAmount,
        ];
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

            if (!empty($params['late_payment_id'])) {
                /** @var LatePaymentRepository $latePaymentRepo */
                $latePaymentRepo = $this->em->getRepository(LatePayment::class);

                /** @var LatePayment $latePayment */
                $latePayment = $latePaymentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(LatePayment::class), $params['late_payment_id']);

                if ($latePayment === null) {
                    throw new LatePaymentNotFoundException();
                }

                $entity->setLatePayment($latePayment);
            } else {
                $entity->setLatePayment(null);
            }

            //////////Payment Received Item/////////////////////////////////////////////////////
            /** @var RpPaymentTypeRepository $paymentTypeRepo */
            $paymentTypeRepo = $this->em->getRepository(RpPaymentType::class);
            /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
            $addedPrivatePayPaymentReceivedItems = [];
            $editedPrivatePayPaymentReceivedItems = [];
            $editedPrivatePayPaymentReceivedItemsIds = [];
            if (!empty($params['resident_private_pay_payment_received_items'])) {
                foreach ($params['resident_private_pay_payment_received_items'] as $privatePayPaymentReceivedItem) {
                    if (empty($privatePayPaymentReceivedItem['id'])) {
                        $addedPrivatePayPaymentReceivedItems[] = $privatePayPaymentReceivedItem;
                    } else {
                        $editedPrivatePayPaymentReceivedItems[$privatePayPaymentReceivedItem['id']] = $privatePayPaymentReceivedItem;
                        $editedPrivatePayPaymentReceivedItemsIds[] = $privatePayPaymentReceivedItem['id'];
                    }
                }
            }

            if ($entity->getResidentPrivatePayPaymentReceivedItems() !== null) {
                /** @var ResidentPrivatePayPaymentReceivedItem $existingPrivatePayPaymentReceivedItem */
                foreach ($entity->getResidentPrivatePayPaymentReceivedItems() as $existingPrivatePayPaymentReceivedItem) {
                    if (\in_array($existingPrivatePayPaymentReceivedItem->getId(), $editedPrivatePayPaymentReceivedItemsIds, false)) {
                        $existingPrivatePayPaymentReceivedItemDate = new \DateTime($editedPrivatePayPaymentReceivedItems[$existingPrivatePayPaymentReceivedItem->getId()]['date']);
                        if ($entity->getCreatedAt()->format('Y') !== $existingPrivatePayPaymentReceivedItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $existingPrivatePayPaymentReceivedItemDate->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }

                        $paymentTypeId = $editedPrivatePayPaymentReceivedItems[$existingPrivatePayPaymentReceivedItem->getId()]['payment_type_id'] ?? 0;
                        $responsiblePersonId = $editedPrivatePayPaymentReceivedItems[$existingPrivatePayPaymentReceivedItem->getId()]['responsible_person_id'] ?? 0;

                        /** @var RpPaymentType $paymentType */
                        $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);
                        /** @var ResidentResponsiblePerson $responsiblePerson */
                        $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $responsiblePersonId);

                        $existingPrivatePayPaymentReceivedItem->setPaymentType($paymentType);
                        $existingPrivatePayPaymentReceivedItem->setResponsiblePerson($responsiblePerson);
                        $existingPrivatePayPaymentReceivedItem->setDate($existingPrivatePayPaymentReceivedItemDate);
                        $existingPrivatePayPaymentReceivedItem->setAmount($editedPrivatePayPaymentReceivedItems[$existingPrivatePayPaymentReceivedItem->getId()]['amount']);
                        $existingPrivatePayPaymentReceivedItem->setTransactionNumber($editedPrivatePayPaymentReceivedItems[$existingPrivatePayPaymentReceivedItem->getId()]['transaction_number']);
                        $existingPrivatePayPaymentReceivedItem->setNotes($editedPrivatePayPaymentReceivedItems[$existingPrivatePayPaymentReceivedItem->getId()]['notes'] ?? '');

                        $this->em->persist($existingPrivatePayPaymentReceivedItem);
                    } else {
                        $entity->removeResidentPrivatePayPaymentReceivedItem($existingPrivatePayPaymentReceivedItem);
                        $this->em->remove($existingPrivatePayPaymentReceivedItem);
                    }
                }
            }

            if (!empty($addedPrivatePayPaymentReceivedItems)) {
                foreach ($addedPrivatePayPaymentReceivedItems as $addedPrivatePayPaymentReceivedItem) {
                    $privatePayPaymentReceivedItemDate = new \DateTime($addedPrivatePayPaymentReceivedItem['date']);
                    if ($entity->getCreatedAt()->format('Y') !== $privatePayPaymentReceivedItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $privatePayPaymentReceivedItemDate->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }

                    $newPrivatePayPaymentReceivedItem = new ResidentPrivatePayPaymentReceivedItem();

                    $paymentTypeId = $addedPrivatePayPaymentReceivedItem['payment_type_id'] ?? 0;
                    $responsiblePersonId = $addedPrivatePayPaymentReceivedItem['responsible_person_id'] ?? 0;

                    /** @var RpPaymentType $paymentType */
                    $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);
                    /** @var ResidentResponsiblePerson $responsiblePerson */
                    $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $responsiblePersonId);

                    $newPrivatePayPaymentReceivedItem->setPaymentType($paymentType);
                    $newPrivatePayPaymentReceivedItem->setResponsiblePerson($responsiblePerson);
                    $newPrivatePayPaymentReceivedItem->setDate($privatePayPaymentReceivedItemDate);
                    $newPrivatePayPaymentReceivedItem->setAmount($addedPrivatePayPaymentReceivedItem['amount']);
                    $newPrivatePayPaymentReceivedItem->setTransactionNumber($addedPrivatePayPaymentReceivedItem['transaction_number']);
                    $newPrivatePayPaymentReceivedItem->setNotes($addedPrivatePayPaymentReceivedItem['notes'] ?? '');
                    $newPrivatePayPaymentReceivedItem->setLedger($entity);
                    $entity->addResidentPrivatePayPaymentReceivedItem($newPrivatePayPaymentReceivedItem);

                    $this->em->persist($newPrivatePayPaymentReceivedItem);
                }
            }

            $addedNotPrivatePayPaymentReceivedItems = [];
            $editedNotPrivatePayPaymentReceivedItems = [];
            $editedNotPrivatePayPaymentReceivedItemsIds = [];
            if (!empty($params['resident_not_private_pay_payment_received_items'])) {
                foreach ($params['resident_not_private_pay_payment_received_items'] as $notPrivatePayPaymentReceivedItem) {
                    if (empty($notPrivatePayPaymentReceivedItem['id'])) {
                        $addedNotPrivatePayPaymentReceivedItems[] = $notPrivatePayPaymentReceivedItem;
                    } else {
                        $editedNotPrivatePayPaymentReceivedItems[$notPrivatePayPaymentReceivedItem['id']] = $notPrivatePayPaymentReceivedItem;
                        $editedNotPrivatePayPaymentReceivedItemsIds[] = $notPrivatePayPaymentReceivedItem['id'];
                    }
                }
            }

            if ($entity->getResidentNotPrivatePayPaymentReceivedItems() !== null) {
                /** @var ResidentNotPrivatePayPaymentReceivedItem $existingNotPrivatePayPaymentReceivedItem */
                foreach ($entity->getResidentNotPrivatePayPaymentReceivedItems() as $existingNotPrivatePayPaymentReceivedItem) {
                    if (\in_array($existingNotPrivatePayPaymentReceivedItem->getId(), $editedNotPrivatePayPaymentReceivedItemsIds, false)) {
                        $existingNotPrivatePayPaymentReceivedItemDate = new \DateTime($editedNotPrivatePayPaymentReceivedItems[$existingNotPrivatePayPaymentReceivedItem->getId()]['date']);
                        if ($entity->getCreatedAt()->format('Y') !== $existingNotPrivatePayPaymentReceivedItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $existingNotPrivatePayPaymentReceivedItemDate->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }

                        $paymentTypeId = $editedNotPrivatePayPaymentReceivedItems[$existingNotPrivatePayPaymentReceivedItem->getId()]['payment_type_id'] ?? 0;

                        /** @var RpPaymentType $paymentType */
                        $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);

                        $existingNotPrivatePayPaymentReceivedItem->setPaymentType($paymentType);
                        $existingNotPrivatePayPaymentReceivedItem->setDate($existingNotPrivatePayPaymentReceivedItemDate);
                        $existingNotPrivatePayPaymentReceivedItem->setAmount($editedNotPrivatePayPaymentReceivedItems[$existingNotPrivatePayPaymentReceivedItem->getId()]['amount']);
                        $existingNotPrivatePayPaymentReceivedItem->setTransactionNumber($editedNotPrivatePayPaymentReceivedItems[$existingNotPrivatePayPaymentReceivedItem->getId()]['transaction_number']);
                        $existingNotPrivatePayPaymentReceivedItem->setNotes($editedNotPrivatePayPaymentReceivedItems[$existingNotPrivatePayPaymentReceivedItem->getId()]['notes'] ?? '');

                        $this->em->persist($existingNotPrivatePayPaymentReceivedItem);
                    } else {
                        $entity->removeResidentNotPrivatePayPaymentReceivedItem($existingNotPrivatePayPaymentReceivedItem);
                        $this->em->remove($existingNotPrivatePayPaymentReceivedItem);
                    }
                }
            }

            if (!empty($addedNotPrivatePayPaymentReceivedItems)) {
                foreach ($addedNotPrivatePayPaymentReceivedItems as $addedNotPrivatePayPaymentReceivedItem) {
                    $notPrivatePayPaymentReceivedItemDate = new \DateTime($addedNotPrivatePayPaymentReceivedItem['date']);
                    if ($entity->getCreatedAt()->format('Y') !== $notPrivatePayPaymentReceivedItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $notPrivatePayPaymentReceivedItemDate->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }

                    $newNotPrivatePayPaymentReceivedItem = new ResidentNotPrivatePayPaymentReceivedItem();

                    $paymentTypeId = $addedNotPrivatePayPaymentReceivedItem['payment_type_id'] ?? 0;

                    /** @var RpPaymentType $paymentType */
                    $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);

                    $newNotPrivatePayPaymentReceivedItem->setPaymentType($paymentType);
                    $newNotPrivatePayPaymentReceivedItem->setDate($notPrivatePayPaymentReceivedItemDate);
                    $newNotPrivatePayPaymentReceivedItem->setAmount($addedNotPrivatePayPaymentReceivedItem['amount']);
                    $newNotPrivatePayPaymentReceivedItem->setTransactionNumber($addedNotPrivatePayPaymentReceivedItem['transaction_number']);
                    $newNotPrivatePayPaymentReceivedItem->setNotes($addedNotPrivatePayPaymentReceivedItem['notes'] ?? '');
                    $newNotPrivatePayPaymentReceivedItem->setLedger($entity);
                    $entity->addResidentNotPrivatePayPaymentReceivedItem($newNotPrivatePayPaymentReceivedItem);

                    $this->em->persist($newNotPrivatePayPaymentReceivedItem);
                }
            }

            $this->validate($entity, null, ['api_admin_resident_ledger_edit']);

            $this->em->persist($entity);
            $this->em->flush();

            $entity = $this->calculateLedgerData($currentSpace, $repo, $entity, $residentId);

            $this->em->persist($entity);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $currentSpace
     * @param ResidentLedgerRepository $repo
     * @param ResidentLedger $entity
     * @param $residentId
     * @return ResidentLedger
     * @throws \Exception
     */
    public function calculateLedgerData($currentSpace, ResidentLedgerRepository $repo, ResidentLedger $entity, $residentId): ResidentLedger
    {
        $calculationDate = $entity->getCreatedAt() ?? new \DateTime('now');

        $awayDays = [];
        $dateStartFormatted = $calculationDate->format('m/01/Y 00:00:00');
        $dateEndFormatted = $calculationDate->format('m/t/Y 23:59:59');

        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);

        /** @var ResidentAwayDaysRepository $residentAwayDaysRepo */
        $residentAwayDaysRepo = $this->em->getRepository(ResidentAwayDays::class);
        $residentAwayDays = $residentAwayDaysRepo->getByInterval($currentSpace, null, $residentId, $dateStart, $dateEnd);
        if (!empty($residentAwayDays)) {
            /** @var ResidentAwayDays $residentAwayDay */
            foreach ($residentAwayDays as $residentAwayDay) {
                $awayDays[] = ImtDateTimeInterval::getWithDateTimes($residentAwayDay->getStart(), $residentAwayDay->getEnd());
            }
        }

        $amountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $calculationDate, $awayDays);

        $entity->setSource($amountData['paymentSources']);
        $entity->setPrivatPaySource($amountData['privatPayPaymentSources']);
        $entity->setNotPrivatPaySource($amountData['notPrivatPayPaymentSources']);
        $entity->setAwayDays($amountData['awayDays']);

        $relationsAmount = $this->calculateRelationsAmount($currentSpace, $entity->getId(), $residentId, $calculationDate);

        //Calculate Privat Pay Balance Due
        $currentMonthPrivatPayBalanceDue = $amountData['privatPayAmount'] + $relationsAmount['privatePayRelationsAmount'];
        //Calculate Not Privat Pay Balance Due
        $currentMonthNotPrivatPayBalanceDue = $amountData['notPrivatPayAmount'] + $relationsAmount['notPrivatePayRelationsAmount'];

        $entity->setPrivatePayBalanceDue(round($currentMonthPrivatPayBalanceDue, 2));
        $entity->setNotPrivatePayBalanceDue(round($currentMonthNotPrivatPayBalanceDue, 2));

        //If all payments have been received set late payment to null
        $priorLedgerData = $this->calculatePriorLedgerData($currentSpace, $repo, $residentId, $entity->getCreatedAt());
        if (round($currentMonthPrivatPayBalanceDue, 2) + $priorLedgerData['priorPrivatPayBalanceDue'] <= 0) {
            $entity->setLatePayment(null);
        }

        return $entity;
    }

    /**
     * @param $id
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function recalculateLedger($id): void
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

            if ($entity->getResident() !== null) {
                $entity = $this->calculateLedgerData($currentSpace, $repo, $entity, $entity->getResident()->getId());
            }

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
