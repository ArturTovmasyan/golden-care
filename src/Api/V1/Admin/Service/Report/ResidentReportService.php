<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\ContractAction;
use App\Entity\Diet;
use App\Entity\Resident;
use App\Entity\ResidentAllergen;
use App\Entity\ResidentDiagnosis;
use App\Entity\ResidentDiet;
use App\Entity\ResidentEvent;
use App\Entity\ResidentMedication;
use App\Entity\ResidentPhysician;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Model\ContractType;
use App\Model\Report\DietaryRestriction;
use App\Model\Report\FaceSheet;
use App\Model\Report\Profile;
use App\Model\Report\ResidentDetailedRoster;
use App\Model\Report\ResidentSimpleRoster;
use App\Model\Report\SixtyDays;
use App\Util\Common\ImtDateTimeInterval;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class ResidentReportService extends BaseService
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
     * @return Profile
     */
    public function getProfileReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        $residents = $this->em->getRepository(Resident::class)->getResidentsFullInfoByTypeOrId($type, $typeId, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $medications = $this->em->getRepository(ResidentMedication::class)->getByResidentIds($residentIds);
        $allergens = $this->em->getRepository(ResidentAllergen::class)->getByResidentIds($residentIds);
        $diagnosis = $this->em->getRepository(ResidentDiagnosis::class)->getByResidentIds($residentIds);
        $responsiblePersons = $this->em->getRepository(ResidentResponsiblePerson::class)->getByResidentIds($residentIds);
        $physicians = $this->em->getRepository(ResidentPhysician::class)->getByResidentIds($type, $residentIds);
        $events = $this->em->getRepository(ResidentEvent::class)->getByResidentIds($residentIds);
        $rents = $this->em->getRepository(ResidentRent::class)->getByResidentIds($residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item['id'];} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            $responsiblePersonPhones = $this->em->getRepository(ResponsiblePersonPhone::class)->getByResponsiblePersonIds($responsiblePersonIds);
        }

        $report = new Profile();
        $report->setResidents($residentsById);
        $report->setMedications($medications);
        $report->setAllergens($allergens);
        $report->setDiagnosis($diagnosis);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);
        $report->setPhysicians($physicians);
        $report->setEvents($events);
        $report->setRents($rents);

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
     * @return FaceSheet
     */
    public function getFaceSheetReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        $residents = $this->em->getRepository(Resident::class)->getResidentsFullInfoByTypeOrId($type, $typeId, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $medications = $this->em->getRepository(ResidentMedication::class)->getByResidentIds($residentIds);
        $allergens = $this->em->getRepository(ResidentAllergen::class)->getByResidentIds($residentIds);
        $diagnosis = $this->em->getRepository(ResidentDiagnosis::class)->getByResidentIds($residentIds);
        $responsiblePersons = $this->em->getRepository(ResidentResponsiblePerson::class)->getByResidentIds($residentIds);
        $physicians = $this->em->getRepository(ResidentPhysician::class)->getByResidentIds($type, $residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item['id'];} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            $responsiblePersonPhones = $this->em->getRepository(ResponsiblePersonPhone::class)->getByResponsiblePersonIds($responsiblePersonIds);
        }

        $report = new FaceSheet();
        $report->setResidents($residentsById);
        $report->setMedications($medications);
        $report->setAllergens($allergens);
        $report->setDiagnosis($diagnosis);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);
        $report->setPhysicians($physicians);

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
     * @return ResidentDetailedRoster
     */
    public function getDetailedRosterReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $residents = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, $typeId);

        $residentIds   = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $physicians = $this->em->getRepository(ResidentPhysician::class)->getByResidentIds($type, $residentIds);
        $responsiblePersons = $this->em->getRepository(ResidentResponsiblePerson::class)->getByResidentIds($residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item['id'];} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            $responsiblePersonPhones = $this->em->getRepository(ResponsiblePersonPhone::class)->getByResponsiblePersonIds($responsiblePersonIds);
        }

        $report = new ResidentDetailedRoster();
        $report->setResidents($residentsById);
        $report->setPhysicians($physicians);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);

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
     * @return ResidentSimpleRoster
     */
    public function getSimpleRosterReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $residents = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, $typeId);
        $typeIds = [];

        if (!empty($residents)) {
            $typeIds = array_map(function($item){return $item['typeId'];} , $residents);
            $typeIds = array_unique($typeIds);
        }

        $report = new ResidentSimpleRoster();
        $report->setResidents($residents);
        $report->setTypeIds($typeIds);
        $report->setStrategyId($type);

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
     * @return DietaryRestriction
     */
    public function getDietaryRestrictionsReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        $residents = $this->em->getRepository(Resident::class)->getDietaryRestrictionsInfo($type, $typeId, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $diets = $this->em->getRepository(ResidentDiet::class)->getByResidentIds($residentIds);
        $data = $this->em->getRepository(Diet::class)->findAll();

        $report = new DietaryRestriction();
        $report->setResidents($residentsById);
        $report->setDiets($diets);
        $report->setData($data);

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
     * @return SixtyDays
     */
    public function getSixtyDaysReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $endDate = new \DateTime('now');
        $endDateFormatted = $endDate->format('m/d/Y');

        if (!empty($date)) {
            $endDate = new \DateTime($date);
            $endDateFormatted = $endDate->format('m/d/Y');
        }

        $startDate = clone $endDate;
        $startDate->sub(new \DateInterval('P2M'));
        $interval = ImtDateTimeInterval::getWithDateTimes($startDate, $endDate);

        $actions = $this->em->getRepository(ContractAction::class)->getResidents60DaysRosterData($type, $interval, $typeId);

        $residentIds = [];

        if (!empty($actions)) {
            $residentIds = array_map(function($item){return $item['id'];} , $actions);
            $residentIds = array_unique($residentIds);
        }

        $responsiblePersons = $this->em->getRepository(ResidentResponsiblePerson::class)->getByResidentIds($residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item['id'];} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            $responsiblePersonPhones = $this->em->getRepository(ResponsiblePersonPhone::class)->getByResponsiblePersonIds($responsiblePersonIds);
        }

        $data = [];
        if (!empty($actions)) {
            foreach ($actions as $action) {
                if ($type !== ContractType::TYPE_APARTMENT) {
                    $actionArray = [
                        'id' => $action['id'],
                        'actionId' => $action['actionId'],
                        'typeId' => $action['typeId'],
                        'typeName' => $action['typeName'],
                        'firstName' => $action['firstName'],
                        'lastName' => $action['lastName'],
                        'admitted' => $action['admitted'],
                        'discharged' => $action['discharged'],
                        'careGroup' => $action['careGroup'],
                        'careLevel' => $action['careLevel'],
                        'rpId' => 'N/A',
                        'rpFullName' => 'N/A',
                        'rpTitle' => 'N/A',
                        'rpPhoneTitle' => 'N/A',
                        'rpPhoneNumber' => 'N/A',
                    ];
                } else {
                    $actionArray = [
                        'id' => $action['id'],
                        'actionId' => $action['actionId'],
                        'typeId' => $action['typeId'],
                        'typeName' => $action['typeName'],
                        'firstName' => $action['firstName'],
                        'lastName' => $action['lastName'],
                        'admitted' => $action['admitted'],
                        'discharged' => $action['discharged'],
                        'rpId' => 'N/A',
                        'rpFullName' => 'N/A',
                        'rpTitle' => 'N/A',
                        'rpPhoneTitle' => 'N/A',
                        'rpPhoneNumber' => 'N/A',
                    ];
                }

                $rpArray = [];
                if (!empty($responsiblePersons)) {
                    foreach ($responsiblePersons as $rp) {
                        if ($rp['residentId'] === $action['id'] && $rp['emergency'] === true) {

                            $rpArray = [
                                'rpId' => $rp['rpId'],
                                'rpFullName' => $rp['firstName'] . ' ' . $rp['lastName'],
                                'rpTitle' => $rp['relationshipTitle'],
                                'rpPhoneTitle' => 'N/A',
                                'rpPhoneNumber' => 'N/A',
                            ];

                            $rpPhone = [];
                            if (!empty($responsiblePersonPhones)) {
                                foreach ($responsiblePersonPhones as $phone) {
                                    if ($phone['rpId'] === $rp['rpId']) {
                                        $rpPhone = [
                                            'rpPhoneTitle' => $phone['type'],
                                            'rpPhoneNumber' => $phone['number'],
                                        ];

                                        if ($phone['type'] == constant('App\\Model\\Phone::TYPE_EMERGENCY')) {
                                            $rpPhone = [
                                                'rpPhoneTitle' => $phone['type'],
                                                'rpPhoneNumber' => $phone['number'],
                                            ];
                                            break;
                                        }
                                    }
                                }
                            }
                            $rpArray = array_merge($rpArray, $rpPhone);
                        }
                    }
                }
                $data[] = array_merge($actionArray, $rpArray);
            }
        }

        $report = new SixtyDays();
        $report->setTitle('60 Days Roster Report');
        $report->setData($data);
        $report->setStrategy(ContractType::getTypes()[$type]);
        $report->setStrategyId($type);
        $report->setDate($endDateFormatted);

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
     * @return \App\Model\Report\ResidentEvent
     */
    public function getEventReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, ContractType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        $dateStart = $dateEnd = new \DateTime('now');
        $dateStartFormatted = $dateStart->format('m/d/Y');
        $dateEndFormatted = $dateEnd->format('m/d/Y');

        if (!empty($dateFrom)) {
            $dateStart = new \DateTime($dateFrom);
            $dateStartFormatted = $dateStart->format('m/d/Y');
        }

        if (!empty($dateTo)) {
            $dateEnd = new \DateTime($dateTo);
            $dateEndFormatted = $dateEnd->format('m/d/Y');
        }

        $residents = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, $typeId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $events = $this->em->getRepository(ResidentEvent::class)->getByResidentIdsAndDate($dateStart, $dateEnd, $residentIds);

        $report = new \App\Model\Report\ResidentEvent();
        $report->setResidents($residentsById);
        $report->setEvents($events);
        $report->setStrategy(ContractType::getTypes()[$type]);
        $report->setStartDate($dateStartFormatted);
        $report->setEndDate($dateEndFormatted);

        return $report;
    }
}