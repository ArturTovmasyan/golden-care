<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Facility;
use App\Entity\FacilityRoomBaseRate;
use App\Entity\FacilityRoomBaseRateCareLevel;
use App\Entity\FacilityRoomType;
use App\Entity\PhysicianPhone;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentAllergen;
use App\Entity\ResidentDiagnosis;
use App\Entity\ResidentDiet;
use App\Entity\ResidentEvent;
use App\Entity\ResidentHealthInsurance;
use App\Entity\ResidentMedicalHistoryCondition;
use App\Entity\ResidentMedication;
use App\Entity\ResidentMedicationAllergy;
use App\Entity\ResidentPhysician;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Model\Report\InvalidRentAmount;
use App\Model\Report\MissingPhysician;
use App\Model\Report\MissingRentRecords;
use App\Model\Report\RentsCurrentVsBase;
use App\Model\Report\ResidentRps;
use App\Model\Report\ResidentSpecial;
use App\Repository\FacilityRoomTypeRepository;
use App\Repository\PhysicianPhoneRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentAllergenRepository;
use App\Repository\ResidentDiagnosisRepository;
use App\Repository\ResidentDietRepository;
use App\Repository\ResidentEventRepository;
use App\Repository\ResidentHealthInsuranceRepository;
use App\Repository\ResidentMedicalHistoryConditionRepository;
use App\Repository\ResidentMedicationAllergyRepository;
use App\Repository\ResidentMedicationRepository;
use App\Repository\ResidentPhysicianRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Repository\ResponsiblePersonPhoneRepository;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class DataHealthReportService extends BaseService
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
     * @param $discontinued
     * @return ResidentRps
     */
    public function getResidentRpsReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): ResidentRps
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = array_map(static function ($item) {
            return $item['id'];
        }, $residents);

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);

        $responsiblePersonResidentIds = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonResidentIds = array_map(static function (ResidentResponsiblePerson $item) {
                return $item->getResident() !== null ? $item->getResident()->getId() : 0;
            }, $responsiblePersons);
            $responsiblePersonResidentIds = array_unique($responsiblePersonResidentIds);
        }

        $residentsById = [];
        foreach ($residents as $resident) {
            if (!\in_array($resident['id'], $responsiblePersonResidentIds, false)) {
                $residentsById[$resident['id']] = $resident;
            }
        }

        $report = new ResidentRps();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($residentsById);
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
     * @param $discontinued
     * @return MissingRentRecords
     */
    public function getMissingRentRecordsReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): MissingRentRecords
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $rents = $rentRepo->getByActiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $type);

        $modifiedRents = [];
        $rentResidentIds = [];
        foreach ($rents as $rent) {
            $rentResidentIds[] = $rent['id'];

            $modifiedRents[$rent['id']][] = [
                'id' => $rent['rentId'],
                'start' => $rent['start'],
                'end' => $rent['end'],
            ];
        }

        $rentResidentIds = array_unique($rentResidentIds);

        $now = new \DateTime('now');
        $nowFormatted = $now->format('Y-m-d');

        $endDateInThePastIds = [];
        foreach ($modifiedRents as $key => $modifiedRent) {
            $isEndDateInThePast = false;
            foreach ($modifiedRent as $rent) {
                if ($rent['end'] !== null && $rent['end']->format('Y-m-d') < $nowFormatted) {
                    $isEndDateInThePast = true;
                } else {
                    $isEndDateInThePast = false;
                    break;
                }
            }

            if ($isEndDateInThePast) {
                $endDateInThePastIds[] = $key;
            }
        }

        $moreThanOneEndDateNullIds = [];
        foreach ($modifiedRents as $key => $modifiedRent) {
            $countMoreThanOneEndDateNull = 0;
            foreach ($modifiedRent as $rent) {
                if ($rent['end'] === null) {
                    ++$countMoreThanOneEndDateNull;
                }
            }

            if ($countMoreThanOneEndDateNull > 1) {
                $moreThanOneEndDateNullIds[] = $key;
            }
        }

        $overlapIds = [];
        foreach ($modifiedRents as $key => $modifiedRent) {
            $isOverlap = false;
            if (\count($modifiedRent) > 1) {
                foreach ($modifiedRent as $rentKey => $rent) {
                    if (array_key_exists($rentKey + 1, $modifiedRent) && $modifiedRent[$rentKey + 1]['start'] >= $modifiedRent[$rentKey]['start'] && ($modifiedRent[$rentKey]['end'] === null ||
                            ($modifiedRent[$rentKey]['end'] !== null && $modifiedRent[$rentKey + 1]['start'] <= $modifiedRent[$rentKey]['start']))) {
                        //Allowed overlap is one day.
                        $isOverlap = $modifiedRent[$rentKey + 1]['start']->format('Y-m-d') !== $modifiedRent[$rentKey]['start']->format('Y-m-d');
                        break;
                    }

                    if (array_key_exists($rentKey + 1, $modifiedRent) && $modifiedRent[$rentKey]['end'] !== null && $modifiedRent[$rentKey]['end'] >= $modifiedRent[$rentKey + 1]['start'] &&
                            ($modifiedRent[$rentKey+1]['end'] === null || ($modifiedRent[$rentKey+1]['end'] !== null && $modifiedRent[$rentKey]['end'] <= $modifiedRent[$rentKey+1]['end']))) {
                        //Allowed overlap is one day.
                        $isOverlap = $modifiedRent[$rentKey]['end']->format('Y-m-d') !== $modifiedRent[$rentKey + 1]['start']->format('Y-m-d');
                        break;
                    }
                }
            }

            if ($isOverlap && !\in_array($key, $moreThanOneEndDateNullIds, false)) {
                $overlapIds[] = $key;
            }
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());

        $report = new MissingRentRecords();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($residents);
        $report->setRentResidentIds($rentResidentIds);
        $report->setEndDateInThePastIds($endDateInThePastIds);
        $report->setMoreThanOneEndDateNullIds($moreThanOneEndDateNullIds);
        $report->setOverlapIds($overlapIds);
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
     * @param $discontinued
     * @return InvalidRentAmount
     */
    public function getInvalidRentAmountReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): InvalidRentAmount
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $residentRents = $rentRepo->getZeroAmountResidentRents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $type, $typeId);

        $activeResidentRents = [];
        $averageRent = [];
        if ((int)$type === GroupType::TYPE_FACILITY) {
            $activeResidents = $rentRepo->getMoreThanZeroAmountActiveResidentRents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $type, $typeId);

            $roomTypeIds = array_map(static function ($item) {
                return $item['roomTypeId'];
            }, $activeResidents);

            $roomTypeIds = array_unique($roomTypeIds);

            /** @var FacilityRoomTypeRepository $roomTypeRepo */
            $roomTypeRepo = $this->em->getRepository(FacilityRoomType::class);

            $roomTypes = $roomTypeRepo->findByIdsWithRates($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $roomTypeIds);

            $roomTypeAverageRent = [];
            /** @var FacilityRoomType $roomType */
            foreach ($roomTypes as $roomType) {
                $roomTypeId = $roomType->getId();

                /** @var FacilityRoomBaseRate $baseRate */
                $baseRate = $roomType->getBaseRates()[0];

                $count = \count($baseRate->getLevels());
                /** @var FacilityRoomBaseRateCareLevel $level */
                foreach ($baseRate->getLevels() as $key => $level) {
                    $roomTypeAverageRent[$roomTypeId] = array_key_exists($roomTypeId, $roomTypeAverageRent) ? $roomTypeAverageRent[$roomTypeId] + $level->getAmount() : $level->getAmount();

                    if ($count > 0 && $key + 1 === $count) {
                        $roomTypeAverageRent[$roomTypeId] /= $count;
                    }
                }
            }

            foreach ($roomTypeIds as $roomTypeId) {
                $averageRent[$roomTypeId] = array_key_exists($roomTypeId, $roomTypeAverageRent) ? $roomTypeAverageRent[$roomTypeId] : 0;
            }

            foreach ($activeResidents as $activeResident) {
                $roomTypeId = $activeResident['roomTypeId'];
                $amount = $activeResident['amount'];

                if ($amount <= $averageRent[$roomTypeId] * 0.7) {
                    $activeResidentRents[] = $activeResident;
                }
            }
        }

        $finalResidents = array_merge($residentRents, $activeResidentRents);

        $typeNames = array_map(static function ($item) {
            return $item['typeName'];
        }, $finalResidents);

        array_multisort($typeNames, SORT_ASC, $finalResidents);

        $report = new InvalidRentAmount();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($finalResidents);
        $report->setAverageRent($averageRent);
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
     * @param $discontinued
     * @return RentsCurrentVsBase
     */
    public function getRentsCurrentVsBaseReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): RentsCurrentVsBase
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if ((int)$type !== GroupType::TYPE_FACILITY) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $activeResidents = $rentRepo->getCurrentRentsByActiveResidents($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $type, $typeId);

        $roomTypeIds = array_map(static function ($item) {
            return $item['roomTypeId'];
        }, $activeResidents);

        $roomTypeIds = array_unique($roomTypeIds);

        /** @var FacilityRoomTypeRepository $roomTypeRepo */
        $roomTypeRepo = $this->em->getRepository(FacilityRoomType::class);

        $roomTypes = $roomTypeRepo->findByIdsWithRates($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $roomTypeIds);

        $baseRates = [];
        /** @var FacilityRoomType $roomType */
        foreach ($roomTypes as $roomType) {
            $roomTypeId = $roomType->getId();

            /** @var FacilityRoomBaseRate $baseRate */
            $baseRate = $roomType->getBaseRates()[0];

            /** @var FacilityRoomBaseRateCareLevel $level */
            foreach ($baseRate->getLevels() as $level) {
                if ($level->getCareLevel() !== null) {
                    $baseRates[$roomTypeId][$level->getCareLevel()->getId()] = $level->getAmount();
                }
            }
        }

        $finalResidents = [];
        foreach ($activeResidents as $resident) {
            $roomTypeId = $resident['roomTypeId'];
            $careLevelId = $resident['careLevelId'];
            $amount = $resident['amount'];

            if (array_key_exists($roomTypeId, $baseRates) && array_key_exists($careLevelId, $baseRates[$roomTypeId])) {
                $value = $amount - $baseRates[$roomTypeId][$careLevelId];

                $finalValue = $value >= 0 ? number_format($value, 2, '.', ',') : '( ' . number_format(abs($value), 2, '.', ',') . ' )';

                $finalResidents[] = [
                    'typeId' => $resident['type_id'],
                    'typeName' => $resident['typeName'],
                    'firstName' => $resident['first_name'],
                    'lastName' => $resident['last_name'],
                    'careLevel' => $resident['careLevel'],
                    'roomType' => $resident['roomType'],
                    'baseRate' => $baseRates[$roomTypeId][$careLevelId],
                    'value' => $finalValue,
                ];
            } else {
                $finalValue = $amount >= 0 ? number_format($amount, 2, '.', ',') : '( ' . number_format(abs($amount), 2, '.', ',') . ' )';

                $finalResidents[] = [
                    'typeId' => $resident['type_id'],
                    'typeName' => $resident['typeName'],
                    'firstName' => $resident['first_name'],
                    'lastName' => $resident['last_name'],
                    'careLevel' => $resident['careLevel'],
                    'roomType' => $resident['roomType'],
                    'baseRate' => 0,
                    'value' => $finalValue,
                ];
            }
        }

        $typeNames = array_map(static function ($item) {
            return $item['typeName'];
        }, $finalResidents);

        array_multisort($typeNames, SORT_ASC, $finalResidents);

        $report = new RentsCurrentVsBase();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($finalResidents);
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
     * @param $discontinued
     * @param $special
     * @return MissingPhysician
     */
    public function getMissingPhysicianReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued, $special): MissingPhysician
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = array_map(static function ($item) {
            return $item['id'];
        }, $residents);

        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

        $physicianResidentIds = [];
        if (!empty($physicians)) {
            $physicianResidentIds = array_map(static function ($item) {
                return $item['residentId'];
            }, $physicians);
            $physicianResidentIds = array_unique($physicianResidentIds);
        }

        $residentsById = [];
        foreach ($residents as $resident) {
            if (!\in_array($resident['id'], $physicianResidentIds, false)) {
                $residentsById[$resident['id']] = $resident;
            }
        }
    
        $report = new MissingPhysician();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($residentsById);
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
     * @param $discontinued
     * @return array
     */
    public function getSpecialResidentReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): array
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = [];
        $residentsById = [];

        $images = $this->getResidentImages([$residentId]);

        foreach ($residents as $resident) {
            $resident['type'] = GroupType::getTypes()[$resident['type']];
            $resident['state'] = AdmissionType::getTypes()[$resident['state']];
            $resident['birthday'] = $resident['birthday']->format('Y-m-d');
            $resident['gender'] = (int)$resident['gender'] > 1 ? 'Female' : 'Male';
            $resident['ssn'] = !empty($resident['ssn']) ? $resident['ssn'] : '';
            $resident['image'] = array_key_exists($residentId, $images) ? $images[$residentId] : '';

            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);
        /** @var ResidentEventRepository $eventRepo */
        $eventRepo = $this->em->getRepository(ResidentEvent::class);
        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
        $admissions = $admissionRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $residentIds, $type);
        $events = $eventRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $residentIds);
        $rents = $rentRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $residentIds);

        $responsiblePersonPhones = [];
        if (!empty($responsiblePersons)) {
            $responsiblePersonIds = array_map(static function (ResidentResponsiblePerson $item) {
                return $item->getResponsiblePerson() ? $item->getResponsiblePerson()->getId() : 0;
            }, $responsiblePersons);
            $responsiblePersonIds = array_unique($responsiblePersonIds);

            /** @var ResponsiblePersonPhoneRepository $responsiblePersonPhoneRepo */
            $responsiblePersonPhoneRepo = $this->em->getRepository(ResponsiblePersonPhone::class);

            $responsiblePersonPhones = $responsiblePersonPhoneRepo->getByResponsiblePersonIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonPhone::class), $responsiblePersonIds);
        }

        $insuranceArray = [];
        $physicians = [];
        $physicianPhones = [];
        $diagnosis = [];
        $medications = [];
        $medicationAllergens = [];
        $allergens = [];
        $diets = [];
        $conditions = [];
        if ($type !== GroupType::TYPE_APARTMENT) {
            /** @var ResidentHealthInsuranceRepository $insuranceRepo */
            $insuranceRepo = $this->em->getRepository(ResidentHealthInsurance::class);
            /** @var ResidentPhysicianRepository $physicianRepo */
            $physicianRepo = $this->em->getRepository(ResidentPhysician::class);
            /** @var ResidentDiagnosisRepository $diagnosisRepo */
            $diagnosisRepo = $this->em->getRepository(ResidentDiagnosis::class);
            /** @var ResidentMedicationRepository $medicationRepo */
            $medicationRepo = $this->em->getRepository(ResidentMedication::class);
            /** @var ResidentMedicationAllergyRepository $medicationAllergenRepo */
            $medicationAllergenRepo = $this->em->getRepository(ResidentMedicationAllergy::class);
            /** @var ResidentAllergenRepository $allergenRepo */
            $allergenRepo = $this->em->getRepository(ResidentAllergen::class);
            /** @var ResidentDietRepository $dietRepo */
            $dietRepo = $this->em->getRepository(ResidentDiet::class);
            /** @var ResidentMedicalHistoryConditionRepository $conditionRepo */
            $conditionRepo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

            $insurances = $insuranceRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $residentIds);
            $medications = $medicationRepo->getWithDiscontinuedByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);
            $medicationAllergens = $medicationAllergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $residentIds);
            $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);
            $diagnosis = $diagnosisRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentIds);
            $diets = $dietRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiet::class), $residentIds);
            $conditions = $conditionRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $residentIds);
            $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

            if (!empty($insurances)) {
                /** @var ResidentHealthInsurance $insurance */
                foreach ($insurances as $insurance) {
                    $insuranceArray[] = [
                        'id' => $insurance->getId(),
                        'medicalRecordNumber' => $insurance->getMedicalRecordNumber(),
                        'groupNumber' => $insurance->getGroupNumber(),
                        'notes' => $insurance->getNotes(),
                        'company' => $insurance->getCompany() !== null ? $insurance->getCompany()->getTitle() : 'N/A',
                        'residentId' => $insurance->getResident() !== null ? $insurance->getResident()->getId() : 0,
                    ];
                }
            }

            if (!empty($physicians)) {
                $physicianIds = array_map(static function ($item) {
                    return $item['pId'];
                }, $physicians);
                $physicianIds = array_unique($physicianIds);

                /** @var PhysicianPhoneRepository $physicianPhoneRepo */
                $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

                $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
            }
        }

        $report = new ResidentSpecial();
        $report->setResidents($residentsById);
        $report->setPhysicianPhones($physicianPhones);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setInsurances($insuranceArray);
        $report->setEvents($events);
        $report->setAdmissions($admissions);
        $report->setRents($rents);
        $report->setPhysicians($physicians);
        $report->setMedications($medications);
        $report->setDiagnosis($diagnosis);
        $report->setMedicationAllergens($medicationAllergens);
        $report->setAllergens($allergens);
        $report->setConditions($conditions);
        $report->setDiets($diets);

        return $report->getResidents()[$residentId];
    }
}