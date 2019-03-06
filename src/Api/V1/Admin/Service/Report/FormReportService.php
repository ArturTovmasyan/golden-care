<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Resident;
use App\Entity\ResidentAllergen;
use App\Entity\ResidentMedication;
use App\Model\ContractType;
use App\Model\Report\BloodPressureCharting;
use App\Model\Report\BowelMovement;
use App\Model\Report\ChangeoverNotes;
use App\Model\Report\Manicure;
use App\Model\Report\MealMonitor;
use App\Model\Report\MedicationChart;
use App\Model\Report\MedicationList;
use App\Model\Report\NightActivity;
use App\Model\Report\ResidentBirthdayList;
use App\Model\Report\RoomAudit;
use App\Model\Report\ShowerSkinInspection;
use App\Repository\ResidentAllergenRepository;
use App\Repository\ResidentMedicationRepository;
use App\Repository\ResidentRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class FormReportService extends BaseService
{
    /**
     * @param $residentAll
     * @param $residentId
     * @param $group
     * @param $groupAll
     * @param $groupId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @return ResidentBirthdayList
     */
    public function getBirthdayListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new ResidentBirthdayList();
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
     * @return BloodPressureCharting
     */
    public function getBloodPressureChartingReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new BloodPressureCharting();
        $report->setTitle('WEIGHT AND BLOOD PRESSURE CHART');
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
     * @return BowelMovement
     */
    public function getBowelMovementReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new BowelMovement();
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
     * @return ChangeoverNotes
     */
    public function getChangeoverNotesReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new ChangeoverNotes();
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
     * @return Manicure
     */
    public function getManicureReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new Manicure();
        $report->setTitle('MANICURE REPORT');
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
     * @return MealMonitor
     */
    public function getMealMonitorReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new MealMonitor();
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
     * @return MedicationChart
     */
    public function getMedicationChartReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, null, $residentId);
        $residentIds = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
        }

        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);

        $medications = $medicationRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);

        /** @var ResidentAllergenRepository $allergenRepo */
        $allergenRepo = $this->em->getRepository(ResidentAllergen::class);

        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);

        $report = new MedicationChart();
        $report->setResidents($residents);
        $report->setMedications($medications);
        $report->setAllergens($allergens);

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
     * @return MedicationList
     */
    public function getMedicationListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);

        $medications = $medicationRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);

        $report = new MedicationList();
        $report->setResidents($residentsById);
        $report->setMedications($medications);

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
     * @return NightActivity
     */
    public function getNightActivityReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new NightActivity();
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
     * @return RoomAudit
     */
    public function getRoomAuditReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $report = new RoomAudit();
        $report->setTitle('ROOM AUDIT REPORT');
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
     * @return ShowerSkinInspection
     */
    public function getShowerSkinInspectionReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId);

        $report = new ShowerSkinInspection();
        $report->setResidents($residents);

        return $report;
    }
}