<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\InvalidEffectiveDateException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerAlreadyExistException;
use App\Api\V1\Common\Service\Exception\ResidentLedgerNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\CreditItem;
use App\Entity\DiscountItem;
use App\Entity\ExpenseItem;
use App\Entity\PaymentSource;
use App\Entity\ResidentAwayDays;
use App\Entity\ResidentCreditItem;
use App\Entity\ResidentDiscountItem;
use App\Entity\ResidentExpenseItem;
use App\Entity\ResidentPaymentReceivedItem;
use App\Entity\ResidentRent;
use App\Entity\Resident;
use App\Entity\ResidentLedger;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\RpPaymentType;
use App\Model\RentPeriod;
use App\Repository\CreditItemRepository;
use App\Repository\DiscountItemRepository;
use App\Repository\ExpenseItemRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\ResidentCreditItemRepository;
use App\Repository\ResidentDiscountItemRepository;
use App\Repository\ResidentExpenseItemRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentPaymentReceivedItemRepository;
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

        $amountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $entity->getResident()->getId(), $entity->getCreatedAt(), null);

        return $amountData['rentData'];
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

            $amountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $now, null);
            //Calculate Privat Pay Balance Due
            $currentMonthPrivatPayBalanceDue = $amountData['privatPayAmount'];
            //Calculate Not Privat Pay Balance Due
            $currentMonthNotPrivatPayBalanceDue = $amountData['notPrivatPayAmount'];

            //////////will be
            //Calculate Amount
            $amount = $amountData['amount'];

            $residentLedger->setAmount(round($amount, 2));

            //Calculate Balance Due
            $currentMonthBalanceDue = $amount;
            //////////remove

            //Calculate Previous Month Balance Due
            $previousDate = new \DateTime(date('Y-m-d', strtotime($now->format('Y-m-d')." first day of previous month")));
            $dateStartFormatted = $previousDate->format('m/01/Y 00:00:00');
            $dateEndFormatted = $previousDate->format('m/t/Y 23:59:59');
            $dateStart = new \DateTime($dateStartFormatted);
            $dateEnd = new \DateTime($dateEndFormatted);

            /** @var ResidentLedger $previousLedger */
            $previousLedger = $repo->getPreviousLedger($currentSpace, null, $residentId, $dateStart, $dateEnd);

            $priorBalanceDue = 0;
            $priorPrivatPayBalanceDue = 0;
            $priorNotPrivatPayBalanceDue = 0;
            if ($previousLedger === null) {
                $priorAmountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $previousDate, null);
                //Calculate Privat Pay Balance Due
                $priorPrivatPayBalanceDue = $priorAmountData['privatPayAmount'];
                //Calculate Not Privat Pay Balance Due
                $priorNotPrivatPayBalanceDue = $priorAmountData['notPrivatPayAmount'];

                //////////will be
                //Calculate Previous Month Amount
                $priorAmount = $priorAmountData['amount'];

                $priorBalanceDue = $priorAmount;
                //////////remove
            }

            $residentLedger->setPrivatePayBalanceDue(round($currentMonthPrivatPayBalanceDue + $priorPrivatPayBalanceDue, 2));
            $residentLedger->setPriorPrivatePayBalanceDue(round($priorPrivatPayBalanceDue, 2));
            $residentLedger->setNotPrivatePayBalanceDue(round($currentMonthNotPrivatPayBalanceDue + $priorNotPrivatPayBalanceDue, 2));
            $residentLedger->setPriorNotPrivatePayBalanceDue(round($priorNotPrivatPayBalanceDue, 2));

            //////////will be
            $residentLedger->setBalanceDue(round($currentMonthBalanceDue + $priorBalanceDue, 2));
            //////////remove

            $residentLedger->setSource($amountData['paymentSources']);
            $residentLedger->setPrivatPaySource($amountData['privatPayPaymentSources']);
            $residentLedger->setNotPrivatPaySource($amountData['notPrivatPayPaymentSources']);

            $this->validate($residentLedger, null, ['api_admin_resident_ledger_add']);

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
        $notPrivatPayPaymentSources = [];
        $rentData = [];
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
                    /** @var PaymentSource $source */
                    foreach ($sources as $source) {
                        $periods[$source->getId()] = $source->getPeriod();

                        if ($source->isPrivatePay()) {
                            $privatePayIds[$source->getId()] = $source->getId();
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

                            $privatPayPaymentSources[] = [
                                'id' => $rentSource['id'],
                                'amount' => round($calcResults['amount'], 2),
                                'responsible_person_id' => array_key_exists('responsible_person_id', $rentSource) ? $rentSource['responsible_person_id'] : '',
                            ];
                        } else {
                            $calcResults = $rentPeriodFactory->calculateForRoomRentInterval(
                                ImtDateTimeInterval::getWithDateTimes($admitted, $discharged),
                                $periods[$rentSource['id']],
                                $rentSource['amount'],
                                $awayDays
                            );

                            $notPrivatPaySourceAmount += $calcResults['amount'];

                            $notPrivatPayPaymentSources[] = [
                                'id' => $rentSource['id'],
                                'amount' => round($calcResults['amount'], 2),
                            ];
                        }

                        $sourceAmount += $calcResults['amount'];

                        $paymentSources[] = $rentSource;
                    }
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
            'privatPayPaymentSources' => $privatPayPaymentSources,
            'notPrivatPayAmount' => $notPrivatPaySourceAmount,
            'notPrivatPayPaymentSources' => $notPrivatPayPaymentSources,
        ];
    }

    /**
     * @param $currentSpace
     * @param $ledgerId
     * @param $now
     * @return array
     * @throws \Exception
     */
    public function calculateRelationsAmount($currentSpace, $ledgerId, $now): array
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

        /** @var ResidentCreditItemRepository $residentCreditItemRepo */
        $residentCreditItemRepo = $this->em->getRepository(ResidentCreditItem::class);
        $residentCreditItems = $residentCreditItemRepo->getByInterval($currentSpace, null, $ledgerId, $dateStart, $dateEnd);

        $creditItemAmount = 0;
        if (!empty($residentCreditItems)) {
            foreach ($residentCreditItems as $creditItem) {
                $creditItemAmount += $creditItem['amount'];
            }
        }

        /** @var ResidentDiscountItemRepository $residentDiscountItemRepo */
        $residentDiscountItemRepo = $this->em->getRepository(ResidentDiscountItem::class);
        $residentDiscountItems = $residentDiscountItemRepo->getByInterval($currentSpace, null, $ledgerId, $dateStart, $dateEnd);

        $discountItemAmount = 0;
        if (!empty($residentDiscountItems)) {
            foreach ($residentDiscountItems as $residentDiscountItem) {
                $discountItemAmount += $residentDiscountItem['amount'];
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

        $privatePayRelationsAmount = $expenseItemAmount - $creditItemAmount - $discountItemAmount - $paymentReceivedItemAmount;
        $notPrivatePayRelationsAmount = -$paymentReceivedItemAmount;

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

            //////////Expense Item/////////////////////////////////////////////////////
            /** @var ExpenseItemRepository $expenseItemRepo */
            $expenseItemRepo = $this->em->getRepository(ExpenseItem::class);
            $addedExpenseItems = [];
            $editedExpenseItems = [];
            $editedExpenseItemsIds = [];
            if (!empty($params['resident_expense_items'])) {
                foreach ($params['resident_expense_items'] as $expenseItem) {
                    if (empty($expenseItem['id'])) {
                        $addedExpenseItems[] = $expenseItem;
                    } else {
                        $editedExpenseItems[$expenseItem['id']] = $expenseItem;
                        $editedExpenseItemsIds[] = $expenseItem['id'];
                    }
                }
            }

            if ($entity->getResidentExpenseItems() !== null) {
                /** @var ResidentExpenseItem $existingExpenseItem */
                foreach ($entity->getResidentExpenseItems() as $existingExpenseItem) {
                    if (\in_array($existingExpenseItem->getId(), $editedExpenseItemsIds, false)) {
                        $existingExpenseItemDate = new \DateTime($editedExpenseItems[$existingExpenseItem->getId()]['date']);
                        if ($entity->getCreatedAt()->format('Y') !== $existingExpenseItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $existingExpenseItemDate->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }

                        $expenseItemId = $editedExpenseItems[$existingExpenseItem->getId()]['expense_item_id'] ?? 0;

                        /** @var ExpenseItem $expenseItem */
                        $expenseItem = $expenseItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $expenseItemId);

                        $existingExpenseItem->setExpenseItem($expenseItem);
                        $existingExpenseItem->setDate($existingExpenseItemDate);
                        $existingExpenseItem->setAmount($editedExpenseItems[$existingExpenseItem->getId()]['amount']);
                        $existingExpenseItem->setNotes($editedExpenseItems[$existingExpenseItem->getId()]['notes'] ?? '');

                        $this->em->persist($existingExpenseItem);
                    } else {
                        $entity->removeResidentExpenseItem($existingExpenseItem);
                        $this->em->remove($existingExpenseItem);
                    }
                }
            }

            if (!empty($addedExpenseItems)) {
                foreach ($addedExpenseItems as $addedExpenseItem) {
                    $expenseItemDate = new \DateTime($addedExpenseItem['date']);
                    if ($entity->getCreatedAt()->format('Y') !== $expenseItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $expenseItemDate->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }

                    $newExpenseItem = new ResidentExpenseItem();

                    $expenseItemId = $addedExpenseItem['expense_item_id'] ?? 0;

                    /** @var ExpenseItem $expenseItem */
                    $expenseItem = $expenseItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ExpenseItem::class), $expenseItemId);

                    $newExpenseItem->setExpenseItem($expenseItem);
                    $newExpenseItem->setDate($expenseItemDate);
                    $newExpenseItem->setAmount($addedExpenseItem['amount']);
                    $newExpenseItem->setNotes($addedExpenseItem['notes'] ?? '');
                    $newExpenseItem->setLedger($entity);
                    $entity->addResidentExpenseItem($newExpenseItem);

                    $this->em->persist($newExpenseItem);
                }
            }

            //////////Credit Item/////////////////////////////////////////////////////
            /** @var CreditItemRepository $creditItemRepo */
            $creditItemRepo = $this->em->getRepository(CreditItem::class);
            $addedCreditItems = [];
            $editedCreditItems = [];
            $editedCreditItemsIds = [];
            if (!empty($params['resident_credit_items'])) {
                foreach ($params['resident_credit_items'] as $creditItem) {
                    if (empty($creditItem['id'])) {
                        $addedCreditItems[] = $creditItem;
                    } else {
                        $editedCreditItems[$creditItem['id']] = $creditItem;
                        $editedCreditItemsIds[] = $creditItem['id'];
                    }
                }
            }

            if ($entity->getResidentCreditItems() !== null) {
                /** @var ResidentCreditItem $existingCreditItem */
                foreach ($entity->getResidentCreditItems() as $existingCreditItem) {
                    if (\in_array($existingCreditItem->getId(), $editedCreditItemsIds, false)) {
                        $existingCreditItemDate = new \DateTime($editedCreditItems[$existingCreditItem->getId()]['date']);
                        if ($entity->getCreatedAt()->format('Y') !== $existingCreditItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $existingCreditItemDate->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }

                        $creditItemId = $editedCreditItems[$existingCreditItem->getId()]['credit_item_id'] ?? 0;

                        /** @var CreditItem $creditItem */
                        $creditItem = $creditItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $creditItemId);

                        $existingCreditItem->setCreditItem($creditItem);
                        $existingCreditItem->setDate($existingCreditItemDate);
                        $existingCreditItem->setAmount($editedCreditItems[$existingCreditItem->getId()]['amount']);
                        $existingCreditItem->setNotes($editedCreditItems[$existingCreditItem->getId()]['notes'] ?? '');

                        $this->em->persist($existingCreditItem);
                    } else {
                        $entity->removeResidentCreditItem($existingCreditItem);
                        $this->em->remove($existingCreditItem);
                    }
                }
            }

            if (!empty($addedCreditItems)) {
                foreach ($addedCreditItems as $addedCreditItem) {
                    $creditItemDate = new \DateTime($addedCreditItem['date']);
                    if ($entity->getCreatedAt()->format('Y') !== $creditItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $creditItemDate->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }

                    $newCreditItem = new ResidentCreditItem();

                    $creditItemId = $addedCreditItem['credit_item_id'] ?? 0;

                    /** @var CreditItem $creditItem */
                    $creditItem = $creditItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CreditItem::class), $creditItemId);

                    $newCreditItem->setCreditItem($creditItem);
                    $newCreditItem->setDate($creditItemDate);
                    $newCreditItem->setAmount($addedCreditItem['amount']);
                    $newCreditItem->setNotes($addedCreditItem['notes'] ?? '');
                    $newCreditItem->setLedger($entity);
                    $entity->addResidentCreditItem($newCreditItem);

                    $this->em->persist($newCreditItem);
                }
            }

            //////////Discount Item/////////////////////////////////////////////////////
            /** @var DiscountItemRepository $discountItemRepo */
            $discountItemRepo = $this->em->getRepository(DiscountItem::class);
            $addedDiscountItems = [];
            $editedDiscountItems = [];
            $editedDiscountItemsIds = [];
            if (!empty($params['resident_discount_items'])) {
                foreach ($params['resident_discount_items'] as $discountItem) {
                    if (empty($discountItem['id'])) {
                        $addedDiscountItems[] = $discountItem;
                    } else {
                        $editedDiscountItems[$discountItem['id']] = $discountItem;
                        $editedDiscountItemsIds[] = $discountItem['id'];
                    }
                }
            }

            if ($entity->getResidentDiscountItems() !== null) {
                /** @var ResidentDiscountItem $existingDiscountItem */
                foreach ($entity->getResidentDiscountItems() as $existingDiscountItem) {
                    if (\in_array($existingDiscountItem->getId(), $editedDiscountItemsIds, false)) {
                        $existingDiscountItemDate = new \DateTime($editedDiscountItems[$existingDiscountItem->getId()]['date']);
                        if ($entity->getCreatedAt()->format('Y') !== $existingDiscountItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $existingDiscountItemDate->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }

                        $discountItemId = $editedDiscountItems[$existingDiscountItem->getId()]['discount_item_id'] ?? 0;

                        /** @var DiscountItem $discountItem */
                        $discountItem = $discountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $discountItemId);

                        $existingDiscountItem->setDiscountItem($discountItem);
                        $existingDiscountItem->setDate($existingDiscountItemDate);
                        $existingDiscountItem->setAmount($editedDiscountItems[$existingDiscountItem->getId()]['amount']);
                        $existingDiscountItem->setNotes($editedDiscountItems[$existingDiscountItem->getId()]['notes'] ?? '');

                        $this->em->persist($existingDiscountItem);
                    } else {
                        $entity->removeResidentDiscountItem($existingDiscountItem);
                        $this->em->remove($existingDiscountItem);
                    }
                }
            }

            if (!empty($addedDiscountItems)) {
                foreach ($addedDiscountItems as $addedDiscountItem) {
                    $discountItemDate = new \DateTime($addedDiscountItem['date']);
                    if ($entity->getCreatedAt()->format('Y') !== $discountItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $discountItemDate->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }

                    $newDiscountItem = new ResidentDiscountItem();

                    $discountItemId = $addedDiscountItem['discount_item_id'] ?? 0;

                    /** @var DiscountItem $discountItem */
                    $discountItem = $discountItemRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiscountItem::class), $discountItemId);

                    $newDiscountItem->setDiscountItem($discountItem);
                    $newDiscountItem->setDate($discountItemDate);
                    $newDiscountItem->setAmount($addedDiscountItem['amount']);
                    $newDiscountItem->setNotes($addedDiscountItem['notes'] ?? '');
                    $newDiscountItem->setLedger($entity);
                    $entity->addResidentDiscountItem($newDiscountItem);

                    $this->em->persist($newDiscountItem);
                }
            }

            //////////Payment Received Item/////////////////////////////////////////////////////
            /** @var RpPaymentTypeRepository $paymentTypeRepo */
            $paymentTypeRepo = $this->em->getRepository(RpPaymentType::class);
            /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
            $addedPaymentReceivedItems = [];
            $editedPaymentReceivedItems = [];
            $editedPaymentReceivedItemsIds = [];
            if (!empty($params['resident_payment_received_items'])) {
                foreach ($params['resident_payment_received_items'] as $paymentReceivedItem) {
                    if (empty($paymentReceivedItem['id'])) {
                        $addedPaymentReceivedItems[] = $paymentReceivedItem;
                    } else {
                        $editedPaymentReceivedItems[$paymentReceivedItem['id']] = $paymentReceivedItem;
                        $editedPaymentReceivedItemsIds[] = $paymentReceivedItem['id'];
                    }
                }
            }

            if ($entity->getResidentPaymentReceivedItems() !== null) {
                /** @var ResidentPaymentReceivedItem $existingPaymentReceivedItem */
                foreach ($entity->getResidentPaymentReceivedItems() as $existingPaymentReceivedItem) {
                    if (\in_array($existingPaymentReceivedItem->getId(), $editedPaymentReceivedItemsIds, false)) {
                        $existingPaymentReceivedItemDate = new \DateTime($editedPaymentReceivedItems[$existingPaymentReceivedItem->getId()]['date']);
                        if ($entity->getCreatedAt()->format('Y') !== $existingPaymentReceivedItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $existingPaymentReceivedItemDate->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }

                        $paymentTypeId = $editedPaymentReceivedItems[$existingPaymentReceivedItem->getId()]['payment_type_id'] ?? 0;
                        $responsiblePersonId = $editedPaymentReceivedItems[$existingPaymentReceivedItem->getId()]['responsible_person_id'] ?? 0;

                        /** @var RpPaymentType $paymentType */
                        $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);
                        /** @var ResidentResponsiblePerson $responsiblePerson */
                        $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $responsiblePersonId);

                        $existingPaymentReceivedItem->setPaymentType($paymentType);
                        $existingPaymentReceivedItem->setResponsiblePerson($responsiblePerson);
                        $existingPaymentReceivedItem->setDate($existingPaymentReceivedItemDate);
                        $existingPaymentReceivedItem->setAmount($editedPaymentReceivedItems[$existingPaymentReceivedItem->getId()]['amount']);
                        $existingPaymentReceivedItem->setTransactionNumber($editedPaymentReceivedItems[$existingPaymentReceivedItem->getId()]['transaction_number']);
                        $existingPaymentReceivedItem->setNotes($editedPaymentReceivedItems[$existingPaymentReceivedItem->getId()]['notes'] ?? '');

                        $this->em->persist($existingPaymentReceivedItem);
                    } else {
                        $entity->removeResidentPaymentReceivedItem($existingPaymentReceivedItem);
                        $this->em->remove($existingPaymentReceivedItem);
                    }
                }
            }

            if (!empty($addedPaymentReceivedItems)) {
                foreach ($addedPaymentReceivedItems as $addedPaymentReceivedItem) {
                    $paymentReceivedItemDate = new \DateTime($addedPaymentReceivedItem['date']);
                    if ($entity->getCreatedAt()->format('Y') !== $paymentReceivedItemDate->format('Y') || $entity->getCreatedAt()->format('m') !== $paymentReceivedItemDate->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }

                    $newPaymentReceivedItem = new ResidentPaymentReceivedItem();

                    $paymentTypeId = $addedPaymentReceivedItem['payment_type_id'] ?? 0;
                    $responsiblePersonId = $editedPaymentReceivedItems[$existingPaymentReceivedItem->getId()]['responsible_person_id'] ?? 0;

                    /** @var RpPaymentType $paymentType */
                    $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(RpPaymentType::class), $paymentTypeId);
                    /** @var ResidentResponsiblePerson $responsiblePerson */
                    $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $responsiblePersonId);

                    $newPaymentReceivedItem->setPaymentType($paymentType);
                    $newPaymentReceivedItem->setResponsiblePerson($responsiblePerson);
                    $newPaymentReceivedItem->setDate($paymentReceivedItemDate);
                    $newPaymentReceivedItem->setAmount($addedPaymentReceivedItem['amount']);
                    $newPaymentReceivedItem->setTransactionNumber($addedPaymentReceivedItem['transaction_number']);
                    $newPaymentReceivedItem->setNotes($addedPaymentReceivedItem['notes'] ?? '');
                    $newPaymentReceivedItem->setLedger($entity);
                    $entity->addResidentPaymentReceivedItem($newPaymentReceivedItem);

                    $this->em->persist($newPaymentReceivedItem);
                }
            }

            //////////Away Days/////////////////////////////////////////////////////
            $addedAwayDays = [];
            $editedAwayDays = [];
            $editedAwayDaysIds = [];
            if (!empty($params['resident_away_days'])) {
                foreach ($params['resident_away_days'] as $awayDay) {
                    if (empty($awayDay['id'])) {
                        $addedAwayDays[] = $awayDay;
                    } else {
                        $editedAwayDays[$awayDay['id']] = $awayDay;
                        $editedAwayDaysIds[] = $awayDay['id'];
                    }
                }
            }

            if ($entity->getResidentAwayDays() !== null) {
                /** @var ResidentAwayDays $existingResidentAwayDay */
                foreach ($entity->getResidentAwayDays() as $existingResidentAwayDay) {
                    if (\in_array($existingResidentAwayDay->getId(), $editedAwayDaysIds, false)) {
                        $existingResidentAwayDayStart = new \DateTime($editedAwayDays[$existingResidentAwayDay->getId()]['start']);
                        $existingResidentAwayDayStart->setTime(0, 0, 0);
                        $existingResidentAwayDayEnd = new \DateTime($editedAwayDays[$existingResidentAwayDay->getId()]['end']);
                        $existingResidentAwayDayEnd->setTime(23, 59, 59);

                        if ($existingResidentAwayDayStart > $existingResidentAwayDayEnd) {
                            throw new StartGreaterEndDateException();
                        }
                        if ($entity->getCreatedAt()->format('Y') !== $existingResidentAwayDayStart->format('Y') || $entity->getCreatedAt()->format('m') !== $existingResidentAwayDayStart->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }
                        if ($entity->getCreatedAt()->format('Y') !== $existingResidentAwayDayEnd->format('Y') || $entity->getCreatedAt()->format('m') !== $existingResidentAwayDayEnd->format('m')) {
                            throw new InvalidEffectiveDateException();
                        }

                        $existingResidentAwayDay->setStart($existingResidentAwayDayStart);
                        $existingResidentAwayDay->setEnd($existingResidentAwayDayEnd);
                        $existingResidentAwayDay->setReason($editedAwayDays[$existingResidentAwayDay->getId()]['reason']);

                        $this->em->persist($existingResidentAwayDay);
                    } else {
                        $entity->removeResidentAwayDays($existingResidentAwayDay);
                        $this->em->remove($existingResidentAwayDay);
                    }
                }
            }

            if (!empty($addedAwayDays)) {
                foreach ($addedAwayDays as $addedAwayDay) {
                    $awayDayStart = new \DateTime($addedAwayDay['start']);
                    $awayDayStart->setTime(0, 0, 0);
                    $awayDayEnd = new \DateTime($addedAwayDay['end']);
                    $awayDayEnd->setTime(23, 59, 59);

                    if ($awayDayStart > $awayDayEnd) {
                        throw new StartGreaterEndDateException();
                    }
                    if ($entity->getCreatedAt()->format('Y') !== $awayDayStart->format('Y') || $entity->getCreatedAt()->format('m') !== $awayDayStart->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }
                    if ($entity->getCreatedAt()->format('Y') !== $awayDayEnd->format('Y') || $entity->getCreatedAt()->format('m') !== $awayDayEnd->format('m')) {
                        throw new InvalidEffectiveDateException();
                    }

                    $newAwayDays = new ResidentAwayDays();

                    $newAwayDays->setStart($awayDayStart);
                    $newAwayDays->setEnd($awayDayEnd);
                    $newAwayDays->setReason($addedAwayDay['reason']);
                    $newAwayDays->setLedger($entity);
                    $entity->addResidentAwayDays($newAwayDays);

                    $this->em->persist($newAwayDays);
                }
            }

            $this->validate($entity, null, ['api_admin_resident_ledger_edit']);

            $this->em->persist($entity);
            $this->em->flush();

            $awayDays = [];
            if ($entity->getResidentAwayDays() !== null) {
                /** @var ResidentAwayDays $residentAwayDay */
                foreach ($entity->getResidentAwayDays() as $residentAwayDay) {
                    $awayDays[] = ImtDateTimeInterval::getWithDateTimes($residentAwayDay->getStart(), $residentAwayDay->getEnd());
                }
            }

            //Calculate amount
            $now = $entity->getCreatedAt() ?? new \DateTime('now');
            $amountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $now, $awayDays);

            $entity->setSource($amountData['paymentSources']);
            $entity->setPrivatPaySource($amountData['privatPayPaymentSources']);
            $entity->setNotPrivatPaySource($amountData['notPrivatPayPaymentSources']);

            //////////will be
            //Calculate amount
            $amount = $amountData['amount'];

            $entity->setAmount(round($amount, 2));
            //////////remove

            $relationsAmount = $this->calculateRelationsAmount($currentSpace, $entity->getId(), $now);

            //Calculate Privat Pay Balance Due
            $currentMonthPrivatPayBalanceDue = $amountData['privatPayAmount'] + $relationsAmount['privatePayRelationsAmount'];
            //Calculate Not Privat Pay Balance Due
            $currentMonthNotPrivatPayBalanceDue = $amountData['notPrivatPayAmount'] + $relationsAmount['notPrivatePayRelationsAmount'];

            //////////will be
            //Calculate Balance Due
            $currentMonthBalanceDue = $amount + $relationsAmount['privatePayRelationsAmount'];
            //////////remove

            //Calculate Previous Month Balance Due
            $previousDate = new \DateTime(date('Y-m-d', strtotime($now->format('Y-m-d')." first day of previous month")));
            $dateStartFormatted = $previousDate->format('m/01/Y 00:00:00');
            $dateEndFormatted = $previousDate->format('m/t/Y 23:59:59');
            $dateStart = new \DateTime($dateStartFormatted);
            $dateEnd = new \DateTime($dateEndFormatted);

            /** @var ResidentLedger $previousLedger */
            $previousLedger = $repo->getPreviousLedger($currentSpace, null, $residentId, $dateStart, $dateEnd);

            $priorBalanceDue = 0;
            $priorPrivatPayBalanceDue = 0;
            $priorNotPrivatPayBalanceDue = 0;
            if ($previousLedger === null) {
                $priorAmountData = $this->calculateAmountAndGetPaymentSources($currentSpace, $residentId, $previousDate, null);
                $priorRelationsAmount = $this->calculateRelationsAmount($currentSpace, $entity->getId(), $previousDate);
                //Calculate Privat Pay Balance Due
                $priorPrivatPayBalanceDue = $priorAmountData['privatPayAmount'] + $priorRelationsAmount['privatePayRelationsAmount'];
                //Calculate Not Privat Pay Balance Due
                $priorNotPrivatPayBalanceDue = $priorAmountData['notPrivatPayAmount'] + $priorRelationsAmount['notPrivatePayRelationsAmount'];

                //////////will be
                //Calculate Previous Month Amount
                $priorAmount = $priorAmountData['amount'];
                $priorBalanceDue = $priorAmount + $priorRelationsAmount['privatePayRelationsAmount'];
                //////////remove
            }

            $entity->setPrivatePayBalanceDue(round($currentMonthPrivatPayBalanceDue + $priorPrivatPayBalanceDue, 2));
            $entity->setPriorPrivatePayBalanceDue(round($priorPrivatPayBalanceDue, 2));
            $entity->setNotPrivatePayBalanceDue(round($currentMonthNotPrivatPayBalanceDue + $priorNotPrivatPayBalanceDue, 2));
            $entity->setPriorNotPrivatePayBalanceDue(round($priorNotPrivatPayBalanceDue, 2));

            //////////will be
            $entity->setBalanceDue(round($currentMonthBalanceDue + $priorBalanceDue, 2));
            //////////remove

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
