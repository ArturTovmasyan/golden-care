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
use App\Model\Report\Payor;
use App\Model\Report\RoomList;
use App\Model\Report\RoomOccupancyRate;
use App\Model\Report\RoomOccupancyRateByMonth;
use App\Model\Report\RoomRent;
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
use App\Repository\ResidentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
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
     * @param $assessmentId
     * @return Payor
     */
    public function getPayorReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
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

        $residentIds = array_map(function($item){return $item['id'];} , $data);

        /** @var ResidentRepository $residentRepo */
        $residentRepo = $this->em->getRepository(Resident::class);

        $residents = $residentRepo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

        $typeIds = array_map(function($item){return $item['typeId'];} , $residents);
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
                        $rent['period'],
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

        $finalData = [];
        foreach ($residents as $resident) {
            foreach ($data as $datum) {
                if ($datum['id'] === $resident['id']) {
                    $resident['rentId'] = $datum['rentId'];
                    $resident['amount'] = $datum['amount'];
                    $resident['period'] = $datum['period'];
                    $resident['sources'] = $datum['sources'];

                    $finalData[] = $resident;
                }
            }

            if(!\in_array($resident['id'], $residentIds, false)) {
                $finalData[] = $resident;
            }
        }

        $report = new Payor();
        $report->setData($finalData);
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
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @return RoomList
     */
    public function getRoomListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
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

        $residentIds = array_map(function($item){return $item['id'];} , $data);

        /** @var ResidentRepository $residentRepo */
        $residentRepo = $this->em->getRepository(Resident::class);

        $residents = $residentRepo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

        $typeIds = array_map(function($item){return $item['typeId'];} , $residents);
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
                        ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m')),
                        $rent['period'],
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']] = $calculationResults['amount'];

                    $sum += $calculationResults['amount'];
                }
            }
            $total[$currentTypeId] = $sum;
        }

        $vacants = $this->getRoomVacancyList($type, $groupAll, $typeId, $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId);

        $finalData = [];
        foreach ($residents as $resident) {
            foreach ($data as $datum) {
                if ($datum['id'] === $resident['id']) {
                    $resident['rentId'] = $datum['rentId'];
                    $resident['amount'] = $datum['amount'];
                    $resident['period'] = $datum['period'];

                    $finalData[] = $resident;
                }
            }

            if(!\in_array($resident['id'], $residentIds, false)) {
                $finalData[] = $resident;
            }
        }

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
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @return RoomRent
     */
    public function getRoomRentReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

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

        $subInterval = ImtDateTimeInterval::getDateDiffForMonthAndYear($reportDate->format('Y'), $reportDate->format('m'));

        $dateStart = $subInterval->getStart()->format('m/d/Y');
        $dateEnd = $subInterval->getEnd()->format('m/d/Y');

        /** @var ResidentRentRepository $repo */
        $repo = $this->em->getRepository(ResidentRent::class);

        $data = $repo->getAdmissionRoomRentData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds());
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);

        $rentResidentIds = array_map(function($item){return $item['id'];} , $data);
        $rentResidentIds = array_unique($rentResidentIds);

        /** @var ResidentRepository $residentRepo */
        $residentRepo = $this->em->getRepository(Resident::class);

        $residents = $residentRepo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

        $residentTypeIds = array_map(function($item){return $item['typeId'];} , $residents);

        $residentIds = array_map(function($item){return $item['id'];} , $residents);

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);

        $changedResidentData = [];
        foreach ($residents as $resident) {
            if (!\in_array($resident['id'], $rentResidentIds, false)) {
                $residentArray = [
                    'fullName' => $resident['firstName'] . ' ' . $resident['lastName'],
                    'number' => array_key_exists('roomNumber', $resident) && array_key_exists('bedNumber', $resident) ? $resident['roomNumber'] . ' ' . $resident['bedNumber'] : null,
                    'actionId' => $resident['actionId'],
                    'id' => 0,
                    'typeName' => $resident['typeName'],
                    'typeId' => $resident['typeId'],
                    'typeShorthand' => $resident['typeShorthand'],
                    'responsiblePerson' => [],
                ];
                $rpResidentArray = array();
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

                    if ($isFinancially === true && $rpResidentId === $resident['id']) {
                        $rpResidentArray['responsiblePerson'][$rpId] = $rpFullName . ' (' . $rpRelationship . ')';
                    }
                }
                $changedResidentData[] = array_merge($residentArray, $rpResidentArray);
            }
        }

        $residentTypeIds = array_unique($residentTypeIds);

        $calcAmount = [];
        $total = [];
        foreach ($residentTypeIds as $residentTypeId) {
            $sum = 0.00;
            foreach ($data as $rent) {
                if ($residentTypeId === $rent['typeId']) {
                    $calculationResults = $rentPeriodFactory->calculateForInterval(
                        ImtDateTimeInterval::getWithDateTimes(new \DateTime($rent['admitted']), new \DateTime($rent['discharged'])),
                        $rent['period'],
                        $rent['amount']
                    );

                    $calcAmount[$rent['id']][$rent['actionId']] = ['days' => $calculationResults['days'], 'amount' => $calculationResults['amount']];

                    $sum += $calculationResults['amount'];
                }
            }
            $total[$residentTypeId] = $sum;
        }

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

        $changedData = array_merge($changedData, $changedResidentData);

        $typeNames = [];
        $numbers = [];
        foreach ($changedData as $k => $changedDatum) {
            $typeNames[$k][] = $changedDatum['typeName'] ?? '';
            $numbers[$k][] = $changedDatum['number'] ?? '';
        }

        array_multisort($typeNames, SORT_ASC, $numbers, SORT_ASC, $changedData);

        $typeIds = array_map(function($item){return $item['typeId'];} , $changedData);
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
        $report->setStrategy(GroupType::getTypes()[$type]);
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
     * @param $assessmentId
     * @return RoomRentMaster
     */
    public function getRoomRentMasterReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
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
        $dateEnd = $subInterval->getEnd()->format('m/d/Y');

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

                if ($type !== GroupType::TYPE_REGION) {

                    $occupancyRate = $this->getRoomOccupancyRateReport($group, $groupAll, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo, null);

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
        $report->setStrategy(GroupType::getTypes()[$type]);
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
     * @param $assessmentId
     * @return RoomRentMasterNew
     */
    public function getRoomRentMasterNewReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
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
        $dateEnd = $subInterval->getEnd()->format('m/d/Y');

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

                if ($type !== GroupType::TYPE_REGION) {
                    $data[$typeId]['occupancy'] = \count($data[$typeId]['occupancies']) === 0 ? 0 : array_sum($data[$typeId]['occupancies']) / \count($data[$typeId]['occupancies']);
                    $data[$typeId]['occupancy'] = number_format($data[$typeId]['occupancy'] * 100, 2);
                    $data[$typeId]['occupancy'] = $data[$typeId]['occupancy'] > 100 ? 100 : $data[$typeId]['occupancy'];

                    $occupancyRate = $this->getRoomOccupancyRateReport($group, $groupAll, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo, null);

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
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @return RoomVacancyList
     */
    public function getRoomVacancyListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $report = new RoomVacancyList();
        $report->setData($this->getRoomVacancyList($group, $groupAll, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId));
        $report->setStrategy(GroupType::getTypes()[$group]);

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
     * @param $assessmentId
     * @return RoomOccupancyRate
     */
    public function getRoomOccupancyRateReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
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

                $roomIds = array_map(function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var FacilityBedRepository $facilityBedRepo */
                $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

                $facilityBeds = $facilityBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $roomIds);

                $ids = [];
                if (\count($facilityBeds)) {
                    $ids = array_map(function($item){return $item['id'];} , $facilityBeds);
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
                        'capacity' => $facility->getCapacity(),
                        'licenseCapacity' => $facility->getLicenseCapacity(),
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

                $roomIds = array_map(function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                /** @var ApartmentBedRepository $apartmentBedRepo */
                $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

                $apartmentBeds = $apartmentBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $roomIds);

                $ids = [];
                if (\count($apartmentBeds)) {
                    $ids = array_map(function($item){return $item['id'];} , $apartmentBeds);
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
        $report->setStrategy(GroupType::getTypes()[$type]);

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
     * @param $assessmentId
     * @return RoomOccupancyRateByMonth
     */
    public function getRoomOccupancyRateByMonthReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $all = $groupAll;
        $type = $group;
        $typeId = $groupId;

        if ($type !== GroupType::TYPE_FACILITY) {
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

        $allTypeIds = array_map(function($item){return $item['typeId'];} , $allData);
        $allTypeIds = array_unique($allTypeIds);

        /** @var FacilityRoomRepository $facilityRoomRepo */
        $facilityRoomRepo = $this->em->getRepository(FacilityRoom::class);

        $rooms = [];
        $facilityBeds = [];
        $typeNames = [];

        if ($typeId) {
            $rooms = $facilityRoomRepo->getBy($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
        }

        if ($all) {
            $rooms = $facilityRoomRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoom::class), $this->grantService->getCurrentUserEntityGrants(Facility::class));
        }

        if (!empty($rooms)) {
            $roomIds = array_map(function (FacilityRoom $item) {
                return $item->getId();
            }, $rooms);

            /** @var FacilityBedRepository $facilityBedRepo */
            $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

            $facilityBeds = $facilityBedRepo->getBedIdAndTypeIdByRooms($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $roomIds);

            $typeNames = array_column($facilityBeds, 'typeName', 'typeId');
            $typeNames = array_unique($typeNames);
        }

        $beds = [];
        $days = [];
        $total = [];
        foreach ($interval as $subVal) {
            foreach ($allTypeIds as $allTypeId) {
                $j = 0;
                $k = 0;
                foreach ($facilityBeds as $bed) {
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
                      'actual' => array_key_exists($allTypeId, $item['actual']) ?  $item['actual'][$allTypeId] : 0,
                      'occupancy' => array_key_exists($allTypeId, $item['potential']) && $item['potential'][$allTypeId] > 0 ? number_format(($item['actual'][$allTypeId] / $item['potential'][$allTypeId]) * 100, 2, $dec_point = '.' , $thousands_sep = '') . '%' : '0%',
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