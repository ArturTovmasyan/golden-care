<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentFormNotFoundException;
use App\Entity\Assessment\Form;
use App\Entity\Facility;
use App\Entity\PhysicianPhone;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAllergen;
use App\Entity\ResidentMedication;
use App\Entity\ResidentMedicationAllergy;
use App\Entity\ResidentPhysician;
use App\Model\GroupType;
use App\Model\Report\AssessmentForm;
use App\Model\Report\BloodPressureCharting;
use App\Model\Report\BowelMovement;
use App\Model\Report\ChangeoverNotes;
use App\Model\Report\Manicure;
use App\Model\Report\MealMonitor;
use App\Model\Report\MedicationChart;
use App\Model\Report\MedicationChartBlank;
use App\Model\Report\MedicationList;
use App\Model\Report\NightActivity;
use App\Model\Report\ResidentBirthdayList;
use App\Model\Report\RoomAudit;
use App\Model\Report\ShowerSkinInspection;
use App\Repository\Assessment\FormRepository;
use App\Repository\FacilityRepository;
use App\Repository\PhysicianPhoneRepository;
use App\Repository\RegionRepository;
use App\Repository\ResidentAllergenRepository;
use App\Repository\ResidentMedicationAllergyRepository;
use App\Repository\ResidentMedicationRepository;
use App\Repository\ResidentPhysicianRepository;
use App\Repository\ResidentRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class FormReportService extends BaseService
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
     * @param $assessmentFormId
     * @return ResidentBirthdayList
     */
    public function getBirthdayListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return BloodPressureCharting
     */
    public function getBloodPressureChartingReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return BowelMovement
     */
    public function getBowelMovementReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ChangeoverNotes
     */
    public function getChangeoverNotesReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return Manicure
     */
    public function getManicureReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return MealMonitor
     */
    public function getMealMonitorReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return MedicationChart
     */
    public function getMedicationChartReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
        }

        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);

        $medications = $medicationRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);

        /** @var ResidentMedicationAllergyRepository $allergenRepo */
        $allergenRepo = $this->em->getRepository(ResidentMedicationAllergy::class);

        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $residentIds);

        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(function($item){return $item['pId'];} , $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
        }

        $report = new MedicationChart();
        $report->setResidents($residents);
        $report->setMedications($medications);
        $report->setAllergens($allergens);
        $report->setResidentPhysicians($physicians);
        $report->setPhysicianPhones($physicianPhones);

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
     * @param $assessmentFormId
     * @return MedicationChartBlank
     */
    public function getMedicationChartBlankReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        $object = null;
        if ($type === GroupType::TYPE_FACILITY) {
            /** @var FacilityRepository $repo */
            $repo = $this->em->getRepository(Facility::class);

            if ($typeId) {
                $object = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $typeId);
            }
        }

        if ($type === GroupType::TYPE_REGION) {
            /** @var RegionRepository $repo */
            $repo = $this->em->getRepository(Region::class);

            if ($typeId) {
                $object = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class), $typeId);
            }
        }

        $report = new MedicationChartBlank();
        $report->setObject($object);

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
     * @param $assessmentFormId
     * @return MedicationChart
     */
    public function getMedicationChartNoAdmissionReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getNoAdmissionResidentById($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);
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

        $physicianPhones = [];
        if (!empty($medications)) {
            $physicianIds = array_map(function($item){return $item['pId'];} , $medications);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
        }

        $report = new MedicationChart();
        $report->setResidents($residents);
        $report->setMedications($medications);
        $report->setAllergens($allergens);
        $report->setPhysicianPhones($physicianPhones);

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
     * @param $assessmentFormId
     * @param $discontinued
     * @return MedicationList
     */
    public function getMedicationListReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);

        $medications = $medicationRepo->getWithDiscontinuedByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);

        $report = new MedicationList();
        $report->setResidents($residentsById);
        $report->setMedications($medications);
        $report->setDiscontinued($discontinued);

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
     * @param $assessmentFormId
     * @param $discontinued
     * @return MedicationList
     */
    public function getMedicationListNoAdmissionReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getNoAdmissionResidentById($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);

        $medications = $medicationRepo->getWithDiscontinuedByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);

        $report = new MedicationList();
        $report->setResidents($residentsById);
        $report->setMedications($medications);
        $report->setDiscontinued($discontinued);

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
     * @param $assessmentFormId
     * @return NightActivity
     */
    public function getNightActivityReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoWithCareGroupByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return RoomAudit
     */
    public function getRoomAuditReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ShowerSkinInspection
     */
    public function getShowerSkinInspectionReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, null, $this->getNotGrantResidentIds());

        $report = new ShowerSkinInspection();
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
     * @param $assessmentId
     * @param $assessmentFormId
     * @return AssessmentForm
     */
    public function getAssessmentFormBlankReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId)
    {
        /** @var FormRepository $repo */
        $repo = $this->em->getRepository(Form::class);

        /** @var Form $form */
        $form = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Form::class), $assessmentFormId);

        if ($form === null) {
            throw new AssessmentFormNotFoundException();
        }

        $careLevelGroups = $form->getCareLevelGroups();

        $report = new AssessmentForm();
        $report->setTitle('Level of Care Assessment');
        $report->setAllGroups($careLevelGroups);
        $report->setResidentFullName('_________________________');
        $report->setDate('_________________________');
        $report->setPerformedBy('_________________________');
        $report->setTable($form->getFormCategories());
        $report->setGroups($careLevelGroups);

        unset($form, $careLevelGroups);

        return $report;
    }
}