<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Diet;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentAllergen;
use App\Entity\ResidentDiagnosis;
use App\Entity\ResidentDiet;
use App\Entity\ResidentEvent;
use App\Entity\ResidentMedication;
use App\Entity\ResidentPhysician;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Model\GroupType;
use App\Model\Report\DietaryRestriction;
use App\Model\Report\FaceSheet;
use App\Model\Report\Profile;
use App\Model\Report\ResidentDetailedRoster;
use App\Model\Report\ResidentSimpleRoster;
use App\Model\Report\SixtyDays;
use App\Repository\DietRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentAllergenRepository;
use App\Repository\ResidentDiagnosisRepository;
use App\Repository\ResidentDietRepository;
use App\Repository\ResidentEventRepository;
use App\Repository\ResidentMedicationRepository;
use App\Repository\ResidentPhysicianRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Repository\ResponsiblePersonPhoneRepository;
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
    public function getProfileReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);
        /** @var ResidentAllergenRepository $allergenRepo */
        $allergenRepo = $this->em->getRepository(ResidentAllergen::class);
        /** @var ResidentDiagnosisRepository $diagnosisRepo */
        $diagnosisRepo = $this->em->getRepository(ResidentDiagnosis::class);
        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);
        /** @var ResidentEventRepository $eventRepo */
        $eventRepo = $this->em->getRepository(ResidentEvent::class);
        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $medications = $medicationRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);
        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);
        $diagnosis = $diagnosisRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentIds);
        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);
        $events = $eventRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $residentIds);
        $rents = $rentRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item->getId();} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            /** @var ResponsiblePersonPhoneRepository $responsiblePersonPhoneRepo */
            $responsiblePersonPhoneRepo = $this->em->getRepository(ResponsiblePersonPhone::class);

            $responsiblePersonPhones = $responsiblePersonPhoneRepo->getByResponsiblePersonIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonPhone::class), $responsiblePersonIds);
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
    public function getFaceSheetReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);
        /** @var ResidentAllergenRepository $allergenRepo */
        $allergenRepo = $this->em->getRepository(ResidentAllergen::class);
        /** @var ResidentDiagnosisRepository $diagnosisRepo */
        $diagnosisRepo = $this->em->getRepository(ResidentDiagnosis::class);
        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $medications = $medicationRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);
        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);
        $diagnosis = $diagnosisRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentIds);
        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item->getId();} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            /** @var ResponsiblePersonPhoneRepository $responsiblePersonPhoneRepo */
            $responsiblePersonPhoneRepo = $this->em->getRepository(ResponsiblePersonPhone::class);

            $responsiblePersonPhones = $responsiblePersonPhoneRepo->getByResponsiblePersonIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonPhone::class), $responsiblePersonIds);
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
    public function getDetailedRosterReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);

        $residentIds   = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);
        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item->getId();} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            /** @var ResponsiblePersonPhoneRepository $responsiblePersonPhoneRepo */
            $responsiblePersonPhoneRepo = $this->em->getRepository(ResponsiblePersonPhone::class);

            $responsiblePersonPhones = $responsiblePersonPhoneRepo->getByResponsiblePersonIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonPhone::class), $responsiblePersonIds);
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
    public function getSimpleRosterReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);
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
    public function getDietaryRestrictionsReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionDietaryRestrictionsInfo($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentDietRepository $residentDietRepo */
        $residentDietRepo = $this->em->getRepository(ResidentDiet::class);
        /** @var DietRepository $dietRepo */
        $dietRepo = $this->em->getRepository(Diet::class);

        $diets = $residentDietRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $residentIds);
        $data = $dietRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(Diet::class));

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
    public function getSixtyDaysReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
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

        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

        $admissions = $admissionRepo->getResidents60DaysRosterData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $interval, $typeId);

        $residentIds = [];

        if (!empty($admissions)) {
            $residentIds = array_map(function($item){return $item['id'];} , $admissions);
            $residentIds = array_unique($residentIds);
        }

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $responsiblePersons = $responsiblePersonRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(function($item){return $item['id'];} , $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            /** @var ResponsiblePersonPhoneRepository $responsiblePersonPhoneRepo */
            $responsiblePersonPhoneRepo = $this->em->getRepository(ResponsiblePersonPhone::class);

            $responsiblePersonPhones = $responsiblePersonPhoneRepo->getByResponsiblePersonIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonPhone::class), $responsiblePersonIds);
        }

        $data = [];
        if (!empty($admissions)) {
            foreach ($admissions as $admission) {
                if ($type !== GroupType::TYPE_APARTMENT) {
                    $admissionArray = [
                        'id' => $admission['id'],
                        'actionId' => $admission['actionId'],
                        'typeId' => $admission['typeId'],
                        'typeName' => $admission['typeName'],
                        'firstName' => $admission['firstName'],
                        'lastName' => $admission['lastName'],
                        'admitted' => $admission['admitted'],
                        'discharged' => $admission['discharged'],
                        'careGroup' => $admission['careGroup'],
                        'careLevel' => $admission['careLevel'],
                        'rpId' => 'N/A',
                        'rpFullName' => 'N/A',
                        'rpTitle' => 'N/A',
                        'rpPhoneTitle' => 'N/A',
                        'rpPhoneNumber' => 'N/A',
                    ];
                } else {
                    $admissionArray = [
                        'id' => $admission['id'],
                        'actionId' => $admission['actionId'],
                        'typeId' => $admission['typeId'],
                        'typeName' => $admission['typeName'],
                        'firstName' => $admission['firstName'],
                        'lastName' => $admission['lastName'],
                        'admitted' => $admission['admitted'],
                        'discharged' => $admission['discharged'],
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
                        if ($rp['residentId'] === $admission['id'] && $rp['emergency'] === true) {

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
                $data[] = array_merge($admissionArray, $rpArray);
            }
        }

        $report = new SixtyDays();
        $report->setTitle('60 Days Roster Report');
        $report->setData($data);
        $report->setStrategy(GroupType::getTypes()[$type]);
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
    public function getEventReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
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

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId);
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentEventRepository $eventRepo */
        $eventRepo = $this->em->getRepository(ResidentEvent::class);

        $events = $eventRepo->getByResidentIdsAndDate($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $dateStart, $dateEnd, $residentIds);

        $report = new \App\Model\Report\ResidentEvent();
        $report->setResidents($residentsById);
        $report->setEvents($events);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStartDate($dateStartFormatted);
        $report->setEndDate($dateEndFormatted);

        return $report;
    }
}