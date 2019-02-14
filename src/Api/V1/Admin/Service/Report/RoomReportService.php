<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\ContractAction;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\PaymentSource;
use App\Entity\Region;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Model\ContractType;
use App\Model\Report\Payor;
use App\Model\Report\RoomList;
use App\Model\Report\RoomOccupancyRate;
use App\Model\Report\RoomRent;
use App\Model\Report\RoomRentMaster;
use App\Model\Report\RoomRentMasterNew;
use App\Model\Report\RoomVacancyList;
use App\Util\Common\ImtDateTimeInterval;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class RoomReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return Payor
     */
    public function getPayorReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $reportDate = new \DateTime('now');
        $reportDateFormatted = $reportDate->format('M/Y');

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
            $reportDateFormatted = $reportDate->format('M/Y');
        }

        $interval = ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m'));

        $data = $this->em->getRepository(ResidentRent::class)->getRentsWithSources($currentSpace, $type, $interval, $typeId);
        $rentPeriodFactory = RentPeriodFactory::getFactory($interval);

        $typeIds = array_map(function($item){return $item['typeId'];} , $data);
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
        foreach ($typeIds as $typeId) {
            $sum = 0.00;
            foreach ($data as $rent) {
                if ($typeId === $rent['typeId']) {
                    $calculationResults = $rentPeriodFactory->calculateForInterval(
                        $interval,
                        $rent['period'],
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']] = $calculationResults['amount'];

                    $sum += $calculationResults['amount'];
                }
            }
            $total[$typeId] = $sum;
        }

        $sources = $this->em->getRepository(PaymentSource::class)->getPaymentSources($currentSpace);

        $report = new Payor();
        $report->setData($data);
        $report->setCalcAmount($calcAmount);
        $report->setPlace($place);
        $report->setTotal($total);
        $report->setSources($sources);
        $report->setStrategy(ContractType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDate($reportDateFormatted);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return RoomList
     */
    public function getRoomListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $reportDate = new \DateTime('now');
        $reportDateFormatted = $reportDate->format('m/d/Y');

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
            $reportDateFormatted = $reportDate->format('m/d/Y');
        }

        $interval = ImtDateTimeInterval::getWithDays($reportDateFormatted, $reportDateFormatted);

        $data = $this->em->getRepository(ResidentRent::class)->getRoomListData($this->grantService->getCurrentSpace(), $type, $interval, $typeId);
        $rentPeriodFactory = RentPeriodFactory::getFactory(ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m')));

        $typeIds = array_map(function($item){return $item['typeId'];} , $data);
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
        foreach ($typeIds as $typeId) {
            $sum = 0.00;
            foreach ($data as $rent) {
                if ($typeId === $rent['typeId']) {
                    $calculationResults = $rentPeriodFactory->calculateForInterval(
                        ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m')),
                        $rent['period'],
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']] = $calculationResults['amount'];

                    $sum += $calculationResults['amount'];
                }
            }
            $total[$typeId] = $sum;
        }

        $report = new RoomList();
        $report->setData($data);
        $report->setCalcAmount($calcAmount);
        $report->setPlace($place);
        $report->setTotal($total);
        $report->setStrategy(ContractType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDate($reportDateFormatted);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return RoomRent
     */
    public function getRoomRentReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $now = new \DateTime('now');
        $reportDate = $now;

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
        }

        $subInterval = ImtDateTimeInterval::getDateDiffForMonthAndYear($reportDate->format('Y'), $reportDate->format('m'));

        $dateStart = $subInterval->getStart()->format('m/d/Y');
        $dateEnd = $subInterval->getEnd()->format('m/d/Y');

        $data = $this->em->getRepository(ResidentRent::class)->getRoomRentData($currentSpace, $type, $subInterval, $typeId);
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);

        $residentIds = array_map(function($item){return $item['id'];} , $data);
        $residentIds = array_unique($residentIds);
        $responsiblePersons = $this->em->getRepository(ResidentResponsiblePerson::class)->getByResidentIds($currentSpace, $residentIds);

        $typeIds = array_map(function($item){return $item['typeId'];} , $data);
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
        foreach ($typeIds as $typeId) {
            $sum = 0.00;
            foreach ($data as $rent) {
                if ($typeId === $rent['typeId']) {
                    $calculationResults = $rentPeriodFactory->calculateForInterval(
                        ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged'])),
                        $rent['period'],
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']][$rent['actionId']] = ['days' => $calculationResults['days'], 'amount' => $calculationResults['amount']];

                    $sum += $calculationResults['amount'];
                }
            }
            $total[$typeId] = $sum;
        }

        //for CSV report
        $changedData = [];
        foreach ($data as $rent) {
            $rentArray = [
                'fullName' => $rent['firstName'] . ' ' . $rent['lastName'],
                'number' => array_key_exists('roomNumber', $rent) && array_key_exists('bedNumber', $rent) ? $rent['roomNumber'] . ' ' . $rent['bedNumber'] : null,
                'period' => $rent['period'],
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
            foreach ($responsiblePersons as $responsiblePerson) {
                if ($responsiblePerson['residentId'] === $rent['id']) {
                    if ($responsiblePerson['financially'] === true) {
                        $rpArray['responsiblePerson'][$responsiblePerson['rpId']] = $responsiblePerson['firstName'] . ' ' . $responsiblePerson['lastName'] . ' (' . $responsiblePerson['relationshipTitle'] . ')';
                    }
                }
            }
            $changedData[] = array_merge($rentArray, $rpArray);
        }

        $csvData = [];
        foreach ($changedData as $changedDatum) {
            $string_version = implode("\r\n", $changedDatum['responsiblePerson']);
            $changedDatum['responsiblePerson'] = $string_version;
            $csvData[] = $changedDatum;
        }

        $report = new RoomRent();
        $report->setData($data);
        $report->setCsvData($csvData);
        $report->setCalcAmount($calcAmount);
        $report->setPlace($place);
        $report->setTotal($total);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setStrategy(ContractType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDateStart($dateStart);
        $report->setDateEnd($dateEnd);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return RoomRentMaster
     */
    public function getRoomRentMasterReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
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
        $dateEnd = $subInterval->getEnd()->format('m/d/Y');

        $types = [];
        switch ($type) {
            case ContractType::TYPE_FACILITY:
                if ($typeId) {
                    $types = $this->em->getRepository(Facility::class)->getBy($currentSpace, $typeId);
                }

                if ($all) {
                    $types = $this->em->getRepository(Facility::class)->orderedFindAll($currentSpace);
                }

                break;
            case ContractType::TYPE_APARTMENT:
                if ($typeId) {
                    $types = $this->em->getRepository(Apartment::class)->getBy($currentSpace, $typeId);
                }

                if ($all) {
                    $types = $this->em->getRepository(Apartment::class)->orderedFindAll($currentSpace);
                }

                break;
            case ContractType::TYPE_REGION:
                if ($typeId) {
                    $types = $this->em->getRepository(Region::class)->getBy($currentSpace, $typeId);
                }

                if ($all) {
                    $types = $this->em->getRepository(Region::class)->orderedFindAll($currentSpace);
                }

                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        $rents = $this->em->getRepository(ResidentRent::class)->getRoomRentMasterData($currentSpace, $type, $subInterval, $typeId);
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);
        $data = [];

        if ($type !== ContractType::TYPE_REGION) {
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
                        $interval = ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged']) );
                        if (!isset($data[$typeId][$rent[$incomePer]])) {
                            $data[$typeId]['occupancy'][$rent[$incomePer]] = 0.00;
                        }
                        $data[$typeId]['occupancy'][$rent[$incomePer]] += $rentPeriodFactory->calculateOccupancyForInterval($interval);
                        $calculationResults = $rentPeriodFactory->calculateForInterval(
                            $interval,
                            $rent['period'],
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

                if ($type !== ContractType::TYPE_REGION) {

                    $occupancyRate = $this->getRoomOccupancyRateReport($group, $groupAll, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo);

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
                    $data[$typeId]['revenue']['Vacant'] = 100 - $data[$typeId]['occupancy'];
                    $data[$typeId]['occ'] = $data[$typeId]['occupancy'];
                    $data[$typeId]['occupancy'] = (float)($data[$typeId]['occupancy'] / 100);
                } else {
                    unset($data[$typeId]['revenue']['Vacant'], $data[$typeId]['occupancy']);
                    foreach ($data[$typeId]['revenue'] as $revenueKey => &$revenueValue) {
                        $revenueValue = number_format($revenueValue * 100, 2);
                    }
                }
            }
        }

        $report = new RoomRentMaster();
        $report->setData($data);
        $report->setStrategy(ContractType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDateStart($dateStart);
        $report->setDateEnd($dateEnd);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return RoomRentMasterNew
     */
    public function getRoomRentMasterNewReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
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
        $dateEnd = $subInterval->getEnd()->format('m/d/Y');

        $types = [];
        switch ($type) {
            case ContractType::TYPE_FACILITY:
                if ($typeId) {
                    $types = $this->em->getRepository(Facility::class)->getBy($currentSpace, $typeId);
                }

                if ($all) {
                    $types = $this->em->getRepository(Facility::class)->orderedFindAll($currentSpace);
                }

                break;
            case ContractType::TYPE_APARTMENT:
                if ($typeId) {
                    $types = $this->em->getRepository(Apartment::class)->getBy($currentSpace, $typeId);
                }

                if ($all) {
                    $types = $this->em->getRepository(Apartment::class)->orderedFindAll($currentSpace);
                }

                break;
            case ContractType::TYPE_REGION:
                if ($typeId) {
                    $types = $this->em->getRepository(Region::class)->getBy($currentSpace, $typeId);
                }

                if ($all) {
                    $types = $this->em->getRepository(Region::class)->orderedFindAll($currentSpace);
                }

                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        $rents = $this->em->getRepository(ResidentRent::class)->getRoomRentMasterNewData($currentSpace, $type, $subInterval, $typeId);
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);
        $data = [];

        if ($type !== ContractType::TYPE_REGION) {
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
                        $interval = ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged']) );
                        if (!isset($data[$typeId]['occupancies'][$rent[$incomePer]])) {
                            $data[$typeId]['occupancies'][$rent[$incomePer]] = 0.00;
                        }
                        $data[$typeId]['occupancies'][$rent[$incomePer]] += $rentPeriodFactory->calculateOccupancyForInterval($interval);
                        $calculationResults = $rentPeriodFactory->calculateForInterval(
                            $interval,
                            $rent['period'],
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

                if ($type !== ContractType::TYPE_REGION) {
                    $data[$typeId]['occupancy'] = \count($data[$typeId]['occupancies']) === 0 ? 0 : array_sum($data[$typeId]['occupancies']) / \count($data[$typeId]['occupancies']);
                    $data[$typeId]['occupancy'] = number_format($data[$typeId]['occupancy'] * 100, 2);
                    $data[$typeId]['occupancy'] = $data[$typeId]['occupancy'] > 100 ? 100 : $data[$typeId]['occupancy'];

                    $occupancyRate = $this->getRoomOccupancyRateReport($group, $groupAll, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo);

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
        $report->setStrategy(ContractType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDateStart($dateStart);
        $report->setDateEnd($dateEnd);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return RoomVacancyList
     */
    public function getRoomVacancyListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_APARTMENT], false)) {
            throw new InvalidParameterException('group');
        }

        $rooms = [];
        $data = [];

        if ($type === ContractType::TYPE_FACILITY) {
            if ($typeId) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->getBy($currentSpace, $typeId);
            }

            if ($all) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->list($currentSpace);
            }

            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                $facilityBeds = $this->em->getRepository(FacilityBed::class)->getBedIdAndTypeIdByRooms($currentSpace, $roomIds);

                if (\count($facilityBeds)) {
                    $bedIds = array_map(function($item){return $item['id'];} , $facilityBeds);

                    $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId($currentSpace, ContractType::TYPE_FACILITY, $bedIds);

                    if (!empty($contractActions)) {
                        $occupancyBedIds = array_map(function($item){return $item['bedId'];} , $contractActions);
                    }

                    foreach ($facilityBeds as $bed) {
                        if (!\in_array($bed['id'], $occupancyBedIds, false)) {
                            $data[] = $bed;
                        }
                    }
                }
            }
        } elseif ($type === ContractType::TYPE_APARTMENT) {
            if ($typeId) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->getBy($currentSpace, $typeId);
            }

            if ($all) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->list($currentSpace);
            }

            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                $apartmentBeds = $this->em->getRepository(ApartmentBed::class)->getBedIdAndTypeIdByRooms($currentSpace, $roomIds);

                if (\count($apartmentBeds)) {
                    $bedIds = array_map(function($item){return $item['id'];} , $apartmentBeds);

                    $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId($currentSpace, ContractType::TYPE_APARTMENT, $bedIds);

                    if (!empty($contractActions)) {
                        $occupancyBedIds = array_map(function($item){return $item['bedId'];} , $contractActions);
                    }

                    foreach ($apartmentBeds as $bed) {
                        if (!\in_array($bed['id'], $occupancyBedIds, false)) {
                            $data[] = $bed;
                        }
                    }
                }
            }
        }

        $report = new RoomVacancyList();
        $report->setData($data);
        $report->setStrategy(ContractType::getTypes()[$type]);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return RoomOccupancyRate
     */
    public function getRoomOccupancyRateReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_APARTMENT], false)) {
            throw new InvalidParameterException('group');
        }

        $rooms = [];
        $types = [];
        $data = [];

        if ($type === ContractType::TYPE_FACILITY) {
            if ($typeId) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->getBy($currentSpace, $typeId);
                $types = $this->em->getRepository(Facility::class)->getBy($currentSpace, $typeId);
            }

            if ($all) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->list($currentSpace);
                $types = $this->em->getRepository(Facility::class)->orderedFindAll($currentSpace);
            }

            $bedIds = [];
            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                $facilityBeds = $this->em->getRepository(FacilityBed::class)->getBedIdAndTypeIdByRooms($currentSpace, $roomIds);

                $ids = [];
                if (\count($facilityBeds)) {
                    $ids = array_map(function($item){return $item['id'];} , $facilityBeds);
                    $bedIds = array_column($facilityBeds, 'typeId', 'id');
                    $bedIds = array_count_values($bedIds);
                }

                $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId($currentSpace, ContractType::TYPE_FACILITY, $ids);

                if (!empty($contractActions)) {
                    $occupancyBedIds = array_column($contractActions, 'typeId', 'bedId');
                    $occupancyBedIds = array_count_values($occupancyBedIds);
                }
            }

            if (!empty($types)) {
                /** @var Facility $facility */
                foreach ($types as $facility) {
                    $data[] = [
                        'typeId' => $facility->getId(),
                        'name' => $facility->getName(),
                        'capacity' => $facility->getCapacity(),
                        'licenseCapacity' => $facility->getLicenseCapacity(),
                        'availableCount' => array_key_exists($facility->getId(), $bedIds) ? $bedIds[$facility->getId()] : 0,
                        'occupiedCount' => array_key_exists($facility->getId(), $occupancyBedIds) ? $occupancyBedIds[$facility->getId()] : 0,
                    ];
                }
            }
        } elseif ($type === ContractType::TYPE_APARTMENT) {
            if ($typeId) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->getBy($currentSpace, $typeId);
                $types = $this->em->getRepository(Apartment::class)->getBy($currentSpace, $typeId);
            }

            if ($all) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->list($currentSpace);
                $types = $this->em->getRepository(Apartment::class)->orderedFindAll($currentSpace);
            }

            $bedIds = [];
            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                $apartmentBeds = $this->em->getRepository(ApartmentBed::class)->getBedIdAndTypeIdByRooms($currentSpace, $roomIds);

                $ids = [];
                if (\count($apartmentBeds)) {
                    $ids = array_map(function($item){return $item['id'];} , $apartmentBeds);
                    $bedIds = array_column($apartmentBeds, 'typeId', 'id');
                    $bedIds = array_count_values($bedIds);
                }

                $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId($currentSpace, ContractType::TYPE_APARTMENT, $ids);

                if (!empty($contractActions)) {
                    $occupancyBedIds = array_column($contractActions, 'typeId', 'bedId');
                    $occupancyBedIds = array_count_values($occupancyBedIds);
                }
            }

            if (!empty($types)) {
                /** @var Apartment $apartment */
                foreach ($types as $apartment) {
                    $data[] = [
                        'typeId' => $apartment->getId(),
                        'name' => $apartment->getName(),
                        'capacity' => $apartment->getCapacity(),
                        'licenseCapacity' => $apartment->getLicenseCapacity(),
                        'availableCount' => array_key_exists($apartment->getId(), $bedIds) ? $bedIds[$apartment->getId()] : 0,
                        'occupiedCount' => array_key_exists($apartment->getId(), $occupancyBedIds) ? $occupancyBedIds[$apartment->getId()] : 0,
                    ];
                }
            }
        }

        $report = new RoomOccupancyRate();
        $report->setData($data);
        $report->setStrategy(ContractType::getTypes()[$type]);

        return $report;
    }
}