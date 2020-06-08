<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\Exception\TimeSpanIsGreaterThan12MonthsException;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\PaymentSource;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePersonRole;
use App\Model\GroupType;
use App\Model\Month;
use App\Model\RentPeriod;
use App\Model\Report\Payor;
use App\Model\Report\RoomList;
use App\Model\Report\RoomOccupancyRate;
use App\Model\Report\RoomOccupancyRateByMonth;
use App\Model\Report\RoomRent;
use App\Model\Report\RoomRentByYear;
use App\Model\Report\RoomRentMaster;
use App\Model\Report\RoomRentMasterNew;
use App\Model\Report\RoomVacancyList;
use App\Repository\ApartmentBedRepository;
use App\Repository\ApartmentRepository;
use App\Repository\ApartmentRoomRepository;
use App\Repository\FacilityBedRepository;
use App\Repository\FacilityRepository;
use App\Repository\FacilityRoomRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\RegionRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Util\Common\ImtDateTimeInterval;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class RoomReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return Payor
     */
    public function getPayorReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): Payor
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $reportDate = new \DateTime('now');
        $reportDateFormatted = $reportDate->format('M/Y');

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
            $reportDateFormatted = $reportDate->format('M/Y');
        }

        $interval = ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m'));

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $data = $repo->getAdmissionRentsWithSources($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $interval, $typeId, $this->getNotGrantResidentIds());
        $rentPeriodFactory = RentPeriodFactory::getFactory($interval);

        $typeIds = array_map(static function ($item) {
            return $item['typeId'];
        }, $data);
        $countTypeIds = array_count_values($typeIds);
        $place = [];
        $i = 0;
        foreach ($countTypeIds as $key => $value) {
            $i += $value;
            $place[$key] = $i;
        }

        $typeIds = array_unique($typeIds);

        $calcAmount = [];
        $total = [];
        foreach ($typeIds as $currentTypeId) {
            $sum = 0.00;
            foreach ($data as $rent) {
                if ($currentTypeId === $rent['typeId']) {
                    $calculationResults = $rentPeriodFactory->calculateForInterval(
                        $interval,
                        RentPeriod::MONTHLY,
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']] = $calculationResults['amount'];

                    $sum += $calculationResults['amount'];
                }
            }
            $total[$currentTypeId] = $sum;
        }

        /** @var PaymentSourceRepository $sourceRepo */
        $sourceRepo = $this->em->getRepository(PaymentSource::class);

        $sources = $sourceRepo->getPaymentSources($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSource::class));

        $report = new Payor();
        $report->setData($data);
        $report->setCalcAmount($calcAmount);
        $report->setPlace($place);
        $report->setTotal($total);
        $report->setSources($sources);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDate($reportDateFormatted);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomList
     */
    public function getRoomListReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $reportDate = new \DateTime('now');
        $reportDateFormatted = $reportDate->format('m/d/Y');

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
            $reportDateFormatted = $reportDate->format('m/d/Y');
        }

        $interval = ImtDateTimeInterval::getWithDays($reportDateFormatted, $reportDateFormatted);

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $data = $repo->getAdmissionRoomListData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $interval, $typeId, $this->getNotGrantResidentIds());
        $rentPeriodFactory = RentPeriodFactory::getFactory(ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m')));

        $rentResidentIds = array_map(static function ($item) {
            return $item['id'];
        }, $data);

        $rentBedIds = array_map(static function ($item) {
            return $item['bedId'];
        }, $data);

        /** @var ResidentAdmissionRepository $residentAdmissionRepo */
        $residentAdmissionRepo = $this->em->getRepository(ResidentAdmission::class);

        $residents = $residentAdmissionRepo->getAdmissionRoomListData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $interval, $typeId, $this->getNotGrantResidentIds());
        $noRentResidents = [];

        if (!empty($residents)) {
            foreach ($residents as $resident) {
                if (!in_array($resident['id'], $rentResidentIds, false) && !in_array($resident['bedId'], $rentBedIds, false)) {
                    $resident['noRent'] = true;
                    $noRentResidents[] = $resident;
                }
            }
        }

        $rentTypeIds = array_map(static function ($item) {
            return $item['typeId'];
        }, $data);
        $dataTypeIds = array_unique($rentTypeIds);

        $calcAmount = [];
        $total = [];
        foreach ($dataTypeIds as $currentTypeId) {
            $sum = 0.00;
            foreach ($data as $rent) {
                $rent['haveRent'] = true;

                if ($currentTypeId === $rent['typeId']) {
                    $calculationResults = $rentPeriodFactory->calculateForInterval(
                        ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m')),
                        RentPeriod::MONTHLY,
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']] = $calculationResults['amount'];

                    $sum += $calculationResults['amount'];
                }
            }
            $total[$currentTypeId] = $sum;
        }

        $mergedData = array_merge($data, $noRentResidents);

        $finalData = [];
        $typeNames = [];
        $numbers = [];
        foreach ($mergedData as $k => $mergedDatum) {
            $number = '';
            if (array_key_exists('roomNumber', $mergedDatum) && array_key_exists('bedNumber', $mergedDatum)) {
                if ($mergedDatum['private']) {
                    $number = $mergedDatum['roomNumber'] . ' ';
                } else {
                    $number = $mergedDatum['roomNumber'] . ' ' . $mergedDatum['bedNumber'];
                }
            }

            $typeNames[$k][] = $mergedDatum['typeName'] ?? '';
            $numbers[$k][] = $number;

            $mergedDatum['period'] = !array_key_exists('noRent', $mergedDatum) ? RentPeriod::MONTHLY : 0;
            $finalData[] = $mergedDatum;
        }

        array_multisort($typeNames, SORT_ASC, $numbers, SORT_ASC, $finalData);

        $typeIds = array_map(static function ($item) {
            return $item['typeId'];
        }, $finalData);
        $countTypeIds = array_count_values($typeIds);
        $place = [];
        $i = 0;
        foreach ($countTypeIds as $key => $value) {
            $i += $value;
            $place[$key] = $i;
        }

        $vacants = $this->getRoomVacancyList($type, $groupAll, $groupIds, $typeId, $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId);

        $report = new RoomList();
        $report->setData($finalData);
        $report->setCalcAmount($calcAmount);
        $report->setPlace($place);
        $report->setTotal($total);
        $report->setVacants($vacants);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDate($reportDateFormatted);
        $report->setSum(array_sum($total));

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomRent
     */
    public function getRoomRentReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomRent
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = (int)$group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $dateStart = $dateEnd = new \DateTime('now');
        $dateStartFormatted = $dateStart->format('m/01/Y 00:00:00');
        $dateEndFormatted = $dateEnd->format('m/t/Y 23:59:59');

        if (!empty($date)) {
            $dateStart = $dateEnd = new \DateTime($date);
            $dateStartFormatted = $dateStart->format('m/01/Y 00:00:00');
            $dateEndFormatted = $dateEnd->format('m/t/Y 23:59:59');
        }

        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);

        if ($dateStart > $dateEnd) {
            throw new StartGreaterEndDateException();
        }

        $subInterval = ImtDateTimeInterval::getWithDateTimes($dateStart, $dateEnd);

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $data = $repo->getAdmissionRoomRentData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds());
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);

        $rentResidentIds = array_map(static function ($item) {
            return $item['id'];
        }, $data);
        $rentResidentIds = array_unique($rentResidentIds);

        $rentTypeIds = array_map(static function ($item) {
            return $item['typeId'];
        }, $data);

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $rentResidentIds);

        $calcAmount = [];
        $total = [];
        $residentCount = [];
        foreach ($rentTypeIds as $rentTypeId) {
            $sum = 0.00;
            $count = [];
            foreach ($data as $rent) {
                if ($rentTypeId === $rent['typeId']) {
                    $calculationResults = $rentPeriodFactory->calculateForRoomRentInterval(
                        ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged'])),
                        RentPeriod::MONTHLY,
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']][$rent['actionId']] = ['days' => $calculationResults['days'], 'amount' => $calculationResults['amount']];

                    $sum += $calculationResults['amount'];

                    $count[] = $rent['id'];
                }
            }
            $total[$rentTypeId] = $sum;

            $count = array_unique($count);
            $residentCount[$rentTypeId] = \count($count);
        }

        $changedData = [];
        foreach ($data as $rent) {
            if (array_key_exists('roomNumber', $rent) && array_key_exists('bedNumber', $rent)) {
                if ($rent['private']) {
                    $number = $rent['roomNumber'] . ' ';
                } else {
                    $number = $rent['roomNumber'] . ' ' . $rent['bedNumber'];
                }
            } else {
                $number = null;
            }

            $rentArray = [
                'fullName' => $rent['firstName'] . ' ' . $rent['lastName'],
                'fullNameShort' => $rent['firstName'] . ' ' . strtoupper($rent['lastName'][0]),
                'number' => $number,
                'period' => RentPeriod::MONTHLY,
                'rentId' => $rent['rentId'],
                'actionId' => $rent['actionId'],
                'amount' => $rent['amount'],
                'id' => $rent['id'],
                'admitted' => $rent['admitted'],
                'discharged' => $rent['discharged'],
                'typeName' => $rent['typeName'],
                'typeId' => $rent['typeId'],
                'typeShorthand' => $rent['typeShorthand'],
                'responsiblePerson' => [],
            ];
            $rpArray = array();
            /** @var ResidentResponsiblePerson $responsiblePerson */
            foreach ($responsiblePersons as $responsiblePerson) {
                $isFinancially = false;
                if (!empty($responsiblePerson->getRoles())) {
                    /** @var ResponsiblePersonRole $role */
                    foreach ($responsiblePerson->getRoles() as $role) {
                        if ($role->isFinancially() === true) {
                            $isFinancially = true;
                        }
                    }
                }

                $rpResidentId = $responsiblePerson->getResident() ? $responsiblePerson->getResident()->getId() : 0;
                $rpId = $responsiblePerson->getResponsiblePerson() ? $responsiblePerson->getResponsiblePerson()->getId() : 0;
                $rpFullName = $responsiblePerson->getResponsiblePerson() ? $responsiblePerson->getResponsiblePerson()->getFirstName() . ' ' . $responsiblePerson->getResponsiblePerson()->getLastName() : '';
                $rpRelationship = $responsiblePerson->getRelationship() ? $responsiblePerson->getRelationship()->getTitle() : '';

                if ($isFinancially === true && $rpResidentId === $rent['id']) {
                    $rpArray['responsiblePerson'][$rpId] = $rpFullName . ' (' . $rpRelationship . ')';
                }
            }
            $changedData[] = array_merge($rentArray, $rpArray);
        }

        $typeNames = [];
        $numbers = [];
        foreach ($changedData as $k => $changedDatum) {
            $typeNames[$k][] = $changedDatum['typeName'] ?? '';
            $numbers[$k][] = $changedDatum['number'] ?? '';
        }

        array_multisort($typeNames, SORT_ASC, $numbers, SORT_ASC, $changedData);

        $typeIds = array_map(static function ($item) {
            return $item['typeId'];
        }, $changedData);
        $countTypeIds = array_count_values($typeIds);
        $place = [];
        $i = 0;
        foreach ($countTypeIds as $key => $value) {
            $i += $value;
            $place[$key] = $i;
        }

        //for CSV report
        $csvData = [];
        foreach ($changedData as $changedDatum) {
            $string_version = implode("\r\n", $changedDatum['responsiblePerson']);
            $changedDatum['responsiblePerson'] = $string_version;
            $csvData[] = $changedDatum;
        }

        $report = new RoomRent();
        $report->setData($changedData);
        $report->setCsvData($csvData);
        $report->setCalcAmount($calcAmount);
        $report->setPlace($place);
        $report->setTotal($total);
        $report->setResidentCount($residentCount);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDateStart($dateStart->format('m/d/Y'));
        $report->setDateEnd($dateEnd->format('m/d/Y'));

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomRentByYear
     */
    public function getRoomRentByYearReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomRentByYear
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = (int)$group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $now = new \DateTime('now');
        if (!empty($date)) {
            $date = new \DateTime($date);

            if ($date->format('Y') ===  $now->format('Y')) {
                $dateStartFormatted = $now->format('01/01/Y 00:00:00');
                $dateEndFormatted = $now->format('m/t/Y 23:59:59');
            } else {
                $dateStartFormatted = $date->format('01/01/Y 00:00:00');
                $dateEndFormatted = $date->format('12/t/Y 23:59:59');
            }
        } else {
            $dateStartFormatted = $now->format('01/01/Y 00:00:00');
            $dateEndFormatted = $now->format('m/t/Y 23:59:59');
        }

        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);
        $dateEndClone = clone $dateEnd;

        if ($dateStart > $dateEnd) {
            throw new StartGreaterEndDateException();
        }

        $interval = [];
        while ($dateEndClone->diff($dateStart)->days > 0 && \count($interval) <= 12) {
            $start = new \DateTime($dateEndClone->format('Y-m-01 00:00:00'));
            $end = new \DateTime($dateEndClone->format('Y-m-t 23:59:59'));

            $interval[] = [
                'subInterval' => ImtDateTimeInterval::getWithDateTimes($start, $end),
                'date' => $start->format('F') . ' ' . $start->format('y')
            ];

            $dateEndClone->modify('last day of previous month');
        }

        $interval = array_reverse($interval);

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $finalData = [];
        $finalCsvData = [];
        $finalCalcAmount = [];
        $finalPlace = [];
        $finalTotal = [];
        $finalResidentCount = [];
        foreach ($interval as $subVal) {
            $subInterval = ImtDateTimeInterval::getWithDateTimes($subVal['subInterval']->getStart(), $subVal['subInterval']->getEnd());
            $rentPeriodFactory = clone RentPeriodFactory::getFactory($subVal['subInterval']);

            $data = $repo->getAdmissionRoomRentData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds());

            $rentResidentIds = array_map(static function ($item) {
                return $item['id'];
            }, $data);
            $rentResidentIds = array_unique($rentResidentIds);

            $rentTypeIds = array_map(static function ($item) {
                return $item['typeId'];
            }, $data);

            /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

            $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $rentResidentIds);

            $calcAmount = [];
            $total = [];
            $residentCount = [];
            foreach ($rentTypeIds as $rentTypeId) {
                $sum = 0.00;
                $count = [];
                foreach ($data as $rent) {
                    if ($rentTypeId === $rent['typeId']) {
                        $calculationResults = $rentPeriodFactory->calculateForRoomRentByYearInterval($subVal['subInterval'],
                            ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged'])),
                            RentPeriod::MONTHLY,
                            $rent['amount']
                        );

                        $calcAmount[$rent['id']][$rent['actionId']] = ['days' => $calculationResults['days'], 'amount' => $calculationResults['amount']];

                        $sum += $calculationResults['amount'];

                        $count[] = $rent['id'];
                    }
                }
                $total[$rentTypeId] = $sum;

                $count = array_unique($count);
                $residentCount[$rentTypeId] = \count($count);
            }

            $changedData = [];
            foreach ($data as $rent) {
                if (array_key_exists('roomNumber', $rent) && array_key_exists('bedNumber', $rent)) {
                    if ($rent['private']) {
                        $number = $rent['roomNumber'] . ' ';
                    } else {
                        $number = $rent['roomNumber'] . ' ' . $rent['bedNumber'];
                    }
                } else {
                    $number = null;
                }

                $rentArray = [
                    'fullName' => $rent['firstName'] . ' ' . $rent['lastName'],
                    'fullNameShort' => $rent['firstName'] . ' ' . strtoupper($rent['lastName'][0]),
                    'number' => $number,
                    'period' => RentPeriod::MONTHLY,
                    'rentId' => $rent['rentId'],
                    'actionId' => $rent['actionId'],
                    'amount' => $rent['amount'],
                    'id' => $rent['id'],
                    'admitted' => $rent['admitted'],
                    'discharged' => $rent['discharged'],
                    'typeName' => $rent['typeName'],
                    'typeId' => $rent['typeId'],
                    'typeShorthand' => $rent['typeShorthand'],
                    'responsiblePerson' => [],
                ];
                $rpArray = array();
                /** @var ResidentResponsiblePerson $responsiblePerson */
                foreach ($responsiblePersons as $responsiblePerson) {
                    $isFinancially = false;
                    if (!empty($responsiblePerson->getRoles())) {
                        /** @var ResponsiblePersonRole $role */
                        foreach ($responsiblePerson->getRoles() as $role) {
                            if ($role->isFinancially() === true) {
                                $isFinancially = true;
                            }
                        }
                    }

                    $rpResidentId = $responsiblePerson->getResident() ? $responsiblePerson->getResident()->getId() : 0;
                    $rpId = $responsiblePerson->getResponsiblePerson() ? $responsiblePerson->getResponsiblePerson()->getId() : 0;
                    $rpFullName = $responsiblePerson->getResponsiblePerson() ? $responsiblePerson->getResponsiblePerson()->getFirstName() . ' ' . $responsiblePerson->getResponsiblePerson()->getLastName() : '';
                    $rpRelationship = $responsiblePerson->getRelationship() ? $responsiblePerson->getRelationship()->getTitle() : '';

                    if ($isFinancially === true && $rpResidentId === $rent['id']) {
                        $rpArray['responsiblePerson'][$rpId] = $rpFullName . ' (' . $rpRelationship . ')';
                    }
                }
                $changedData[] = array_merge($rentArray, $rpArray);
            }

            $typeNames = [];
            $numbers = [];
            foreach ($changedData as $k => $changedDatum) {
                $typeNames[$k][] = $changedDatum['typeName'] ?? '';
                $numbers[$k][] = $changedDatum['number'] ?? '';
            }

            array_multisort($typeNames, SORT_ASC, $numbers, SORT_ASC, $changedData);

            $typeIds = array_map(static function ($item) {
                return $item['typeId'];
            }, $changedData);
            $countTypeIds = array_count_values($typeIds);
            $place = [];
            $i = 0;
            foreach ($countTypeIds as $key => $value) {
                $i += $value;
                $place[$key] = $i;
            }

            //for CSV report
            $csvData = [];
            foreach ($changedData as $changedDatum) {
                $string_version = implode("\r\n", $changedDatum['responsiblePerson']);
                $changedDatum['responsiblePerson'] = $string_version;
                $csvData[] = $changedDatum;
            }

            $finalData[$subVal['date']] = $changedData;
            $finalCsvData[$subVal['date']] = $csvData;
            $finalCalcAmount[$subVal['date']] = $calcAmount;
            $finalPlace[$subVal['date']] = $place;
            $finalTotal[$subVal['date']] = $total;
            $finalResidentCount[$subVal['date']] = $residentCount;
        }

        $report = new RoomRentByYear();
        $report->setData($finalData);
        $report->setCsvData($finalCsvData);
        $report->setCalcAmount($finalCalcAmount);
        $report->setPlace($finalPlace);
        $report->setTotal($finalTotal);
        $report->setResidentCount($finalResidentCount);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomRentMaster
     */
    public function getRoomRentMasterReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomRentMaster
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $now = new \DateTime('now');
        $reportDate = $now;

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
        }

        $month = $reportDate->format('m');
        $year = $reportDate->format('Y');

        $subInterval = ImtDateTimeInterval::getWithMonthAndYear($year, $month);

        $dateStart = $subInterval->getStart()->format('m/d/Y');
        $dateEnd = $subInterval->getEnd() !== null ? $subInterval->getEnd()->format('m/d/Y') : '';

        $types = [];
        switch ($type) {
            case GroupType::TYPE_FACILITY:
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                if ($typeId) {
                    $types = $facilityRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
                }

                if ($all) {
                    $types = $facilityRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
                }

                break;
            case GroupType::TYPE_APARTMENT:
                /** @var ApartmentRepository $apartmentRepo */
                $apartmentRepo = $this->em->getRepository(Apartment::class);

                if ($typeId) {
                    $types = $apartmentRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class), $typeId);
                }

                if ($all) {
                    $types = $apartmentRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class));
                }

                break;
            case GroupType::TYPE_REGION:
                /** @var RegionRepository $regionRepo */
                $regionRepo = $this->em->getRepository(Region::class);

                if ($typeId) {
                    $types = $regionRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class), $typeId);
                }

                if ($all) {
                    $types = $regionRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class));
                }

                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $rents = $repo->getAdmissionRoomRentMasterData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds());
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);
        $data = [];

        if ($type !== GroupType::TYPE_REGION) {
            $incomePer = 'bedId';
        } else {
            $incomePer = 'id';
        }

        if (!empty($types)) {
            foreach ($types as $value) {
                $typeId = $value->getId();

                $data[$typeId] = array(
                    'sum' => 0.00,
                    'typeName' => $value->getName(),
                    'typeShorthand' => $value->getShorthand(),
                    'avgRent' => 0.00,
                    'occ' => 0.00,
                    'ave' => 0.00,
                    'revenue' => array(
                        'Vacant' => 0,
                        '< 1k' => 0,
                        '1k < 2k' => 0,
                        '2k < 3k' => 0,
                        '3k < 4k' => 0,
                        '4k < 5k' => 0,
                        '> 5k' => 0,
                    )
                );
                $sum = 0.00;
                $paymentsCount = 0;

                foreach ($rents as $rent) {
                    if ($typeId === $rent['typeId']) {
                        $interval = ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged']));
                        if (!isset($data[$typeId][$rent[$incomePer]])) {
                            $data[$typeId]['occupancy'][$rent[$incomePer]] = 0.00;
                        }
                        $data[$typeId]['occupancy'][$rent[$incomePer]] += $rentPeriodFactory->calculateOccupancyForInterval($interval);
                        $calculationResults = $rentPeriodFactory->calculateForInterval(
                            $interval,
                            RentPeriod::MONTHLY,
                            $rent['amount']
                        );
                        $amount = $calculationResults['amount'];

                        if ($amount <= 1000) {
                            $data[$typeId]['revenue']['< 1k']++;
                        } elseif (1001 <= $amount && $amount <= 2000) {
                            $data[$typeId]['revenue']['1k < 2k']++;
                        } elseif (2001 <= $amount && $amount <= 3000) {
                            $data[$typeId]['revenue']['2k < 3k']++;
                        } elseif (3001 <= $amount && $amount <= 4000) {
                            $data[$typeId]['revenue']['3k < 4k']++;
                        } elseif (4001 <= $amount && $amount <= 5000) {
                            $data[$typeId]['revenue']['4k < 5k']++;
                        } else {
                            $data[$typeId]['revenue']['> 5k']++;
                        }

                        if ($amount > 0) {
                            $paymentsCount++;
                        }
                        $sum += $amount;
                    }
                }

                $data[$typeId]['sum'] = number_format($sum, 2);
                $data[$typeId]['ave'] = $paymentsCount > 0 ? $sum / $paymentsCount : 0;
                $data[$typeId]['avgRent'] = number_format($data[$typeId]['ave'], 2, '.', null);

                if ($type !== GroupType::TYPE_REGION) {

                    $occupancyRate = $this->getRoomOccupancyRateReport($group, $groupAll, $groupIds, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo, null, null);

                    $availableCount = [];
                    foreach ($occupancyRate->getData() as $val) {
                        if ($val['typeId'] === $typeId) {
                            $availableCount[$typeId] = $val['availableCount'];
                        }
                    }

                    $data[$typeId]['roomsCount'] = $roomsCount = $availableCount[$typeId];

                    $data[$typeId]['occupancy'] = !isset($data[$typeId]['occupancy']) || $roomsCount === 0 ? 0 : array_sum($data[$typeId]['occupancy']) / $roomsCount;
                    $data[$typeId]['occupancy'] = number_format($data[$typeId]['occupancy'] * 100, 2, '.', null);

                    $revenueAll = array_sum($data[$typeId]['revenue']);
                    foreach ($data[$typeId]['revenue'] as $revenueKey => &$revenueValue) {
                        $revenueValue = $revenueAll === 0 ? 0 : ($revenueValue / $revenueAll) * $data[$typeId]['occupancy'];
                    }
                    unset($revenueValue);
                    $data[$typeId]['revenue']['Vacant'] = 100 - $data[$typeId]['occupancy'];
                    $data[$typeId]['occ'] = $data[$typeId]['occupancy'];
                    $data[$typeId]['occupancy'] = (float)($data[$typeId]['occupancy'] / 100);
                } else {
                    unset($data[$typeId]['revenue']['Vacant'], $data[$typeId]['occupancy']);
                    foreach ($data[$typeId]['revenue'] as $revenueKey => &$revenueValue) {
                        $revenueValue = number_format($revenueValue * 100, 2);
                    }
                    unset($revenueValue);
                }
            }
        }

        $report = new RoomRentMaster();
        $report->setData($data);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDateStart($dateStart);
        $report->setDateEnd($dateEnd);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomRentMasterNew
     */
    public function getRoomRentMasterNewReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomRentMasterNew
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $now = new \DateTime('now');
        $reportDate = $now;

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
        }

        $month = $reportDate->format('m');
        $year = $reportDate->format('Y');

        if (is_numeric($month) && $month > 0 && $month < 12 && is_numeric($year) && $year > 2000 && $year <= $now->format('Y')) {
            $subInterval = ImtDateTimeInterval::getWithMonthAndYear($year, $month);
        } else {
            $subInterval = ImtDateTimeInterval::getWithDateTimes(new \DateTime('2010-01-01 00:00:00'), new \DateTime('now'));
        }

        $dateStart = $subInterval->getStart()->format('m/d/Y');
        $dateEnd = $subInterval->getEnd() !== null ? $subInterval->getEnd()->format('m/d/Y') : '';

        $types = [];
        switch ($type) {
            case GroupType::TYPE_FACILITY:
                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                if ($typeId) {
                    $types = $facilityRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
                }

                if ($all) {
                    $types = $facilityRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
                }

                break;
            case GroupType::TYPE_APARTMENT:
                /** @var ApartmentRepository $apartmentRepo */
                $apartmentRepo = $this->em->getRepository(Apartment::class);

                if ($typeId) {
                    $types = $apartmentRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class), $typeId);
                }

                if ($all) {
                    $types = $apartmentRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class));
                }

                break;
            case GroupType::TYPE_REGION:
                /** @var RegionRepository $regionRepo */
                $regionRepo = $this->em->getRepository(Region::class);

                if ($typeId) {
                    $types = $regionRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class), $typeId);
                }

                if ($all) {
                    $types = $regionRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class));
                }

                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $rents = $repo->getAdmissionRoomRentMasterNewData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds());
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);
        $data = [];

        if ($type !== GroupType::TYPE_REGION) {
            $incomePer = 'bedId';
        } else {
            $incomePer = 'id';
        }

        if (!empty($types)) {
            foreach ($types as $value) {
                $typeId = $value->getId();

                $data[$typeId] = array(
                    'typeName' => $value->getName(),
                    'grossRevenue' => 0.00,
                    'avgNum' => 0.00,
                    'incomePer' => 0.00,
                    'incomes' => [],
                    'occupancy' => 0.00,
                    'occupancies' => [],
                );
                $sum = 0.00;

                foreach ($rents as $rent) {
                    if ($typeId === $rent['typeId']) {
                        $interval = ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged']));
                        if (!isset($data[$typeId]['occupancies'][$rent[$incomePer]])) {
                            $data[$typeId]['occupancies'][$rent[$incomePer]] = 0.00;
                        }
                        $data[$typeId]['occupancies'][$rent[$incomePer]] += $rentPeriodFactory->calculateOccupancyForInterval($interval);
                        $calculationResults = $rentPeriodFactory->calculateForInterval(
                            $interval,
                            RentPeriod::MONTHLY,
                            $rent['amount']
                        );
                        $amount = $calculationResults['amount'];
                        if ($amount > 0) {
                            if (!isset($data[$typeId]['incomes'][$rent[$incomePer]])) {
                                $data[$typeId]['incomes'][$rent[$incomePer]] = [];
                            }
                            $data[$typeId]['incomes'][$rent[$incomePer]][] = $amount;
                        }
                        $sum += $amount;
                    }
                }
                foreach ($data[$typeId]['incomes'] as $incomePerId => $incomes) {
                    $data[$typeId]['incomes'][$incomePerId] = array_sum($data[$typeId]['incomes'][$incomePerId]);
                }

                if ($type !== GroupType::TYPE_REGION) {
                    $data[$typeId]['occupancy'] = \count($data[$typeId]['occupancies']) === 0 ? 0 : array_sum($data[$typeId]['occupancies']) / \count($data[$typeId]['occupancies']);
                    $data[$typeId]['occupancy'] = number_format($data[$typeId]['occupancy'] * 100, 2);
                    $data[$typeId]['occupancy'] = $data[$typeId]['occupancy'] > 100 ? 100 : $data[$typeId]['occupancy'];

                    $occupancyRate = $this->getRoomOccupancyRateReport($group, $groupAll, $groupIds, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo, null, null);

                    foreach ($occupancyRate->getData() as $val) {
                        if ($val['typeId'] === $typeId) {
                            $availableCount = $val['availableCount'];

                            $data[$typeId]['avgNum'] = $availableCount === 0 ? 0 : (100 - $data[$typeId]['occupancy']) * $availableCount / 100;
                            $data[$typeId]['avgNum'] = number_format($data[$typeId]['avgNum'], 2);
                            $data[$typeId]['avgNum'] = $data[$typeId]['avgNum'] < 0 ? 0 : $data[$typeId]['avgNum'];
                        }
                    }
                }
                $data[$typeId]['incomePer'] = \count($data[$typeId]['incomes']) === 0 ? 0 : array_sum($data[$typeId]['incomes']) / \count($data[$typeId]['incomes']);
                $data[$typeId]['incomePer'] = number_format($data[$typeId]['incomePer'], 2);
                $data[$typeId]['grossRevenue'] = number_format($sum, 2);
                unset($data[$typeId]['incomes'], $data[$typeId]['occupancies']);
            }
        }

        $report = new RoomRentMasterNew();
        $report->setData($data);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDateStart($dateStart);
        $report->setDateEnd($dateEnd);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomVacancyList
     */
    public function getRoomVacancyListReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomVacancyList
    {
        $report = new RoomVacancyList();
        $report->setData($this->getRoomVacancyList($group, $groupAll, $groupIds, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId));
        $report->setStrategy(GroupType::getTypes()[$group]);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomOccupancyRate
     */
    public function getRoomOccupancyRateReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomOccupancyRate
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_APARTMENT], false)) {
            throw new InvalidParameterException('group');
        }

        $rooms = [];
        $types = [];
        $data = [];

        if ($type === GroupType::TYPE_FACILITY) {
            /** @var FacilityRoomRepository $facilityRoomRepo */
            $facilityRoomRepo = $this->em->getRepository(FacilityRoom::class);

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            if ($typeId) {
                $rooms = $facilityRoomRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
                $types = $facilityRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
            }

            if ($all) {
                $rooms = $facilityRoomRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class));
                $types = $facilityRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
            }

            $bedIds = [];
            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(static function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var FacilityBedRepository $facilityBedRepo */
                $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

                $facilityBeds = $facilityBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $roomIds);

                $ids = [];
                if (\count($facilityBeds)) {
                    $ids = array_map(static function ($item) {
                        return $item['id'];
                    }, $facilityBeds);
                    $bedIds = array_column($facilityBeds, 'typeId', 'id');
                    $bedIds = array_count_values($bedIds);
                }

                $admissions = $admissionRepo->getBedIdAndTypeId($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $ids);

                if (!empty($admissions)) {
                    $occupancyBedIds = array_column($admissions, 'typeId', 'bedId');
                    $occupancyBedIds = array_count_values($occupancyBedIds);
                }
            }

            if (!empty($types)) {
                /** @var Facility $facility */
                foreach ($types as $facility) {
                    $data[] = [
                        'typeId' => $facility->getId(),
                        'name' => $facility->getName(),
                        'bedsTarget' => $facility->getBedsTarget(),
                        'bedsLicensed' => $facility->getBedsLicensed(),
                        'availableCount' => array_key_exists($facility->getId(), $bedIds) ? $bedIds[$facility->getId()] : 0,
                        'occupiedCount' => array_key_exists($facility->getId(), $occupancyBedIds) ? $occupancyBedIds[$facility->getId()] : 0,
                    ];
                }
            }
        } elseif ($type === GroupType::TYPE_APARTMENT) {
            /** @var ApartmentRoomRepository $apartmentRoomRepo */
            $apartmentRoomRepo = $this->em->getRepository(ApartmentRoom::class);

            /** @var ApartmentRepository $apartmentRepo */
            $apartmentRepo = $this->em->getRepository(Apartment::class);

            if ($typeId) {
                $rooms = $apartmentRoomRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $typeId);
                $types = $apartmentRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class), $typeId);
            }

            if ($all) {
                $rooms = $apartmentRoomRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class));
                $types = $apartmentRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class));
            }

            $bedIds = [];
            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(static function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var ApartmentBedRepository $apartmentBedRepo */
                $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

                $apartmentBeds = $apartmentBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $roomIds);

                $ids = [];
                if (\count($apartmentBeds)) {
                    $ids = array_map(static function ($item) {
                        return $item['id'];
                    }, $apartmentBeds);
                    $bedIds = array_column($apartmentBeds, 'typeId', 'id');
                    $bedIds = array_count_values($bedIds);
                }

                $admissions = $admissionRepo->getBedIdAndTypeId($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_APARTMENT, $ids);

                if (!empty($admissions)) {
                    $occupancyBedIds = array_column($admissions, 'typeId', 'bedId');
                    $occupancyBedIds = array_count_values($occupancyBedIds);
                }
            }

            if (!empty($types)) {
                /** @var Apartment $apartment */
                foreach ($types as $apartment) {
                    $data[] = [
                        'typeId' => $apartment->getId(),
                        'name' => $apartment->getName(),
                        'bedsTarget' => $apartment->getBedsTarget(),
                        'bedsLicensed' => $apartment->getBedsLicensed(),
                        'availableCount' => array_key_exists($apartment->getId(), $bedIds) ? $bedIds[$apartment->getId()] : 0,
                        'occupiedCount' => array_key_exists($apartment->getId(), $occupancyBedIds) ? $occupancyBedIds[$apartment->getId()] : 0,
                    ];
                }
            }
        }

        $report = new RoomOccupancyRate();
        $report->setData($data);
        $report->setStrategy(GroupType::getTypes()[$type]);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomOccupancyRateByMonth
     */
    public function getRoomOccupancyRateByMonthReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): RoomOccupancyRateByMonth
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_APARTMENT], false)) {
            throw new InvalidParameterException('group');
        }

        $dateStart = $dateEnd = new \DateTime('now');
        $dateStartFormatted = $dateStart->format('m/01/Y 00:00:00');
        $dateEndFormatted = $dateEnd->format('m/t/Y 23:59:59');

        if (!empty($dateFrom)) {
            $dateStart = new \DateTime($dateFrom);
            $dateStartFormatted = $dateStart->format('m/01/Y 00:00:00');
        }

        if (!empty($dateTo)) {
            $dateEnd = new \DateTime($dateTo);
            $dateEndFormatted = $dateEnd->format('m/t/Y 23:59:59');
        }

        $dateStart = new \DateTime($dateStartFormatted);
        $dateEnd = new \DateTime($dateEndFormatted);
        $dateEndClone = clone $dateEnd;

        if ($dateStart > $dateEnd) {
            throw new StartGreaterEndDateException();
        }

        $diff = $dateEnd->diff($dateStart);
        $diffYear = (int)$diff->format('%Y');
        $diffMonth = (int)$diff->format('%m');

        if ($diffYear * 12 + $diffMonth >= 12) {
            throw new TimeSpanIsGreaterThan12MonthsException();
        }

        $interval = [];
        while ($dateEndClone->diff($dateStart)->days > 0 && \count($interval) <= 12) {
            $start = new \DateTime($dateEndClone->format('Y-m-01 00:00:00'));
            $end = new \DateTime($dateEndClone->format('Y-m-t 23:59:59'));

            $interval[] = [
                'subInterval' => ImtDateTimeInterval::getWithDateTimes($start, $end),
                'monthNumber' => $start->format('n')
            ];

            $dateEndClone->modify('last day of previous month');
        }

        $interval = array_reverse($interval);

        $subInterval = ImtDateTimeInterval::getWithDateTimes($dateStart, $dateEnd);

        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $allData = $repo->getRoomOccupancyRateByMonthData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds());

        $allTypeIds = array_map(static function ($item) {
            return $item['typeId'];
        }, $allData);
        $allTypeIds = array_unique($allTypeIds);

        $rooms = [];
        $roomBeds = [];
        $typeNames = [];
        if ($type === GroupType::TYPE_FACILITY) {
            /** @var FacilityRoomRepository $facilityRoomRepo */
            $facilityRoomRepo = $this->em->getRepository(FacilityRoom::class);

            if ($typeId) {
                $rooms = $facilityRoomRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
            }

            if ($all) {
                $rooms = $facilityRoomRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class));
            }

            if (!empty($rooms)) {
                $roomIds = array_map(static function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var FacilityBedRepository $facilityBedRepo */
                $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

                $roomBeds = $facilityBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $roomIds);

                $typeNames = array_column($roomBeds, 'typeName', 'typeId');
                $typeNames = array_unique($typeNames);
            }
        } elseif ($type === GroupType::TYPE_APARTMENT) {
            /** @var ApartmentRoomRepository $apartmentRoomRepo */
            $apartmentRoomRepo = $this->em->getRepository(ApartmentRoom::class);

            if ($typeId) {
                $rooms = $apartmentRoomRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class), $typeId);
            }

            if ($all) {
                $rooms = $apartmentRoomRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentRoom::class), $this->grantService->getCurrentUserEntityGrants(Apartment::class));
            }

            if (!empty($rooms)) {
                $roomIds = array_map(static function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var ApartmentBedRepository $apartmentBedRepo */
                $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

                $roomBeds = $apartmentBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $roomIds);

                $typeNames = array_column($roomBeds, 'typeName', 'typeId');
                $typeNames = array_unique($typeNames);
            }
        }

        $beds = [];
        $days = [];
        $total = [];
        foreach ($interval as $subVal) {
            foreach ($allTypeIds as $allTypeId) {
                $j = 0;
                $k = 0;
                foreach ($roomBeds as $bed) {
                    if ($bed['typeId'] === $allTypeId) {
                        ++$j;
                        $k = $j * ($subVal['subInterval']->getEnd()->diff($subVal['subInterval']->getStart())->days + 1);
                    }
                }

                $beds[$allTypeId] = $k;
                $days[$allTypeId] = 0;
            }

            $total[$subVal['monthNumber']]['potential'] = $beds;

            $rentPeriodFactory = RentPeriodFactory::getFactory($subVal['subInterval']);

            $totalDays = [];
            foreach ($allTypeIds as $typeId) {

                $sumDays = 0;
                foreach ($allData as $admission) {
                    if ($admission['typeId'] === $typeId) {
                        $calculationResults = $rentPeriodFactory->calculateForReportInterval(
                            ImtDateTimeInterval::getWithDateTimes($admission['admitted'], $admission['discharged']),
                            $subVal['subInterval']
                        );

                        $sumDays += $calculationResults['days'];
                    }
                }
                $totalDays[$typeId] = $sumDays;
            }
            $total[$subVal['monthNumber']]['actual'] = !empty($totalDays) ? $totalDays : $days;
        }

        $data = [];
        foreach ($allTypeIds as $allTypeId) {
            foreach ($total as $key => $item) {
                $data[$allTypeId][$key] = [
                    'month' => Month::getTypes()[$key],
                    'name' => array_key_exists($allTypeId, $typeNames) ? $typeNames[$allTypeId] : 'N/A',
                    'potential' => $item['potential'][$allTypeId],
                    'actual' => array_key_exists($allTypeId, $item['actual']) ? $item['actual'][$allTypeId] : 0,
                    'occupancy' => array_key_exists($allTypeId, $item['potential']) && $item['potential'][$allTypeId] > 0 ? number_format(($item['actual'][$allTypeId] / $item['potential'][$allTypeId]) * 100, 2, $dec_point = '.', $thousands_sep = '') . '%' : '0%',
                ];
            }
        }

        $report = new RoomOccupancyRateByMonth();
        $report->setData($data);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDateStart($dateStartFormatted);
        $report->setDateEnd($dateEndFormatted);

        return $report;
    }
}