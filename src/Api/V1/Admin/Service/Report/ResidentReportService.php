<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Allergen;
use App\Entity\Diagnosis;
use App\Entity\Medication;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentEvent;
use App\Entity\ResidentRent;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Model\ContractType;
use App\Model\Report\DietaryRestriction;
use App\Model\Report\FaceSheet;
use App\Model\Report\Profile;
use App\Model\Report\ResidentDetailedRoster;
use App\Model\Report\ResidentSimpleRoster;
use App\Model\Report\SixtyDays;
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

        $medications = $this->em->getRepository(Medication::class)->getByResidentIds($residentIds);
        $allergens = $this->em->getRepository(Allergen::class)->getByResidentIds($residentIds);
        $diagnosis = $this->em->getRepository(Diagnosis::class)->getByResidentIds($residentIds);
        $responsiblePersons = $this->em->getRepository(ResponsiblePerson::class)->getByResidentIds($residentIds);
        $physicians = $this->em->getRepository(Physician::class)->getByResidentIds($residentIds);
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

        $medications = $this->em->getRepository(Medication::class)->getByResidentIds($residentIds);
        $allergens = $this->em->getRepository(Allergen::class)->getByResidentIds($residentIds);
        $diagnosis = $this->em->getRepository(Diagnosis::class)->getByResidentIds($residentIds);
        $responsiblePersons = $this->em->getRepository(ResponsiblePerson::class)->getByResidentIds($residentIds);
        $physicians = $this->em->getRepository(Physician::class)->getByResidentIds($residentIds);

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

        $physicians = $this->em->getRepository(Physician::class)->getByResidentIds($residentIds);
        $responsiblePersons = $this->em->getRepository(ResponsiblePerson::class)->getByResidentIds($residentIds);

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

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        $residents = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, false, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $report = new ResidentSimpleRoster();
        $report->setResidents($residents);

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

        $residents = $this->em->getRepository(Resident::class)->getDietaryRestrictionsInfo($type, $typeId);

        $report = new DietaryRestriction();
        $report->setResidents($residents);

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

        list($m1, $d1, $y1) = explode('/', $date);

        if (!checkdate($m1, $d1, $y1)) {
            throw new InvalidParameterException('start_date');
        }

        $endDate   = \DateTime::createFromFormat('m/d/Y', $date);
        $startDate = clone $endDate;
        $startDate->sub(new \DateInterval('P2M'));
        $startDate->setTime(0, 0);
        $endDate->setTime(23, 59);

        $data = $this->em->getRepository(Resident::class)->getResidentContracts($startDate, $endDate, $type, $typeId);

        $report = new SixtyDays();
        $report->setTitle('60 Days Roster Report');
        $report->setDate($date);
        $report->setContracts($data);

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

        list($m1, $d1, $y1) = explode('/', $dateFrom);
        list($m2, $d2, $y2) = explode('/', $dateTo);

        if (!checkdate($m1, $d1, $y1) || !checkdate($m2, $d2, $y2)) {
            throw new InvalidParameterException('start_date, end_date');
        }

        $dateFrom = \DateTime::createFromFormat('m/d/Y', $dateFrom);
        $dateTo   = \DateTime::createFromFormat('m/d/Y', $dateTo);

        $events = $this->em->getRepository(ResidentEvent::class)->getByPeriodAndType($dateFrom, $dateTo, $type, $typeId);

        $report = new \App\Model\Report\ResidentEvent();
        $report->setEvents($events);

        return $report;
    }
}