<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\StartGreaterEndDateException;
use App\Api\V1\Common\Service\Exception\TimeSpanIsGreaterThan12MonthsException;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\Diet;
use App\Entity\ResidentHealthInsurance;
use App\Entity\PhysicianPhone;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentAllergen;
use App\Entity\ResidentDiagnosis;
use App\Entity\ResidentDiet;
use App\Entity\ResidentEvent;
use App\Entity\ResidentMedication;
use App\Entity\ResidentMedicationAllergy;
use App\Entity\ResidentPhysician;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Entity\ResponsiblePersonRole;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Model\Report\DietaryRestriction;
use App\Model\Report\FaceSheet;
use App\Model\Report\Profile;
use App\Model\Report\ResidentDetailedRoster;
use App\Model\Report\ResidentEvent as ReportResidentEvent;
use App\Model\Report\ResidentMoveByMonth;
use App\Model\Report\ResidentSimpleRoster;
use App\Model\Report\ResponsiblePersonEmails;
use App\Model\Report\SixtyDays;
use App\Repository\DietRepository;
use App\Repository\ResidentHealthInsuranceRepository;
use App\Repository\PhysicianPhoneRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentAllergenRepository;
use App\Repository\ResidentDiagnosisRepository;
use App\Repository\ResidentDietRepository;
use App\Repository\ResidentEventRepository;
use App\Repository\ResidentMedicationAllergyRepository;
use App\Repository\ResidentMedicationRepository;
use App\Repository\ResidentPhysicianRepository;
use App\Repository\ResidentRentRepository;
use App\Repository\ResidentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Repository\ResponsiblePersonPhoneRepository;
use App\Util\Common\ImtDateTimeInterval;
use DataURI\Data;
use DataURI\Dumper;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class ResidentReportService extends BaseService
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
     * @return Profile
     */
    public function getProfileReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): Profile
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentHealthInsuranceRepository $insuranceRepo */
        $insuranceRepo = $this->em->getRepository(ResidentHealthInsurance::class);
        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);
        /** @var ResidentMedicationAllergyRepository $medicationAllergenRepo */
        $medicationAllergenRepo = $this->em->getRepository(ResidentMedicationAllergy::class);
        /** @var ResidentAllergenRepository $allergenRepo */
        $allergenRepo = $this->em->getRepository(ResidentAllergen::class);
        /** @var ResidentDiagnosisRepository $diagnosisRepo */
        $diagnosisRepo = $this->em->getRepository(ResidentDiagnosis::class);
        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);
        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);
        /** @var ResidentEventRepository $eventRepo */
        $eventRepo = $this->em->getRepository(ResidentEvent::class);
        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);

        $insurances = $insuranceRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $residentIds);
        $medications = $medicationRepo->getWithDiscontinuedByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);
        $medicationAllergens = $medicationAllergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $residentIds);
        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);
        $diagnosis = $diagnosisRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentIds);
        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);
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

        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(static function ($item) {
                return $item['pId'];
            }, $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
        }

        $insuranceArray = [];
        $insuranceFiles = [];
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

                if ($this->getInsuranceFirstImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceFirstImage($insurance);
                }

                if ($this->getInsuranceSecondImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceSecondImage($insurance);
                }
            }
        }

        $report = new Profile();
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setResidents($residentsById);
        $report->setInsurances($insuranceArray);
        $report->setInsuranceFiles($insuranceFiles);
        $report->setMedications($medications);
        $report->setMedicationAllergens($medicationAllergens);
        $report->setAllergens($allergens);
        $report->setDiagnosis($diagnosis);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);
        $report->setPhysicians($physicians);
        $report->setPhysicianPhones($physicianPhones);
        $report->setAdmissions($admissions);
        $report->setEvents($events);
        $report->setRents($rents);
        $report->setDiscontinued($discontinued);
        $report->setImages($this->getResidentImages($residentIds));

        return $report;
    }

    /**
     * @param ResidentHealthInsurance $insurance
     * @return null|string
     */
    public function getInsuranceFirstImage(ResidentHealthInsurance $insurance): ?string
    {
        $image = null;

        if ($insurance->getResident() && $insurance->getFirstFile() !== null) {
            if ($insurance->getFirstFile()->getMimeType() === 'application/pdf') {
                $first = $this->s3Service->downloadFile($insurance->getFirstFile()->getS3Id(), $insurance->getFirstFile()->getType());

                $img = new \Imagick();
                $img->setResolution(300, 300);
                $img->setCompression(\Imagick::COMPRESSION_JPEG);
                $img->setCompressionQuality(100);

                if ($first !== null) {
                    /** @var Stream $firstStream */
                    $firstStream = $first['Body'];

                    $img1 = new \Imagick();
                    $img1->setResolution(300, 300);
                    $img1->readImageBlob($firstStream->getContents());
                    while ($img1->hasPreviousImage()) {
                        $img1->removeImage();
                    }
                    $img->addImage($img1);
                }

                $random_name = '/tmp/hif_' . md5($insurance->getFirstFile()->getId()) . '_' . (new \DateTime())->format('Ymd_His') . '.jpeg';
                $img->setImageFormat('jpeg');
                $img->writeImage($random_name);
                $img->destroy();

                if (file_exists($random_name)) {
                    $dataObject = Data::buildFromFile($random_name);

                    $image = Dumper::dump($dataObject);
                }
            } else {
                $image = $this->s3Service->getFile($insurance->getFirstFile()->getS3Id(), $insurance->getFirstFile()->getType());
            }
        }

        return $image;
    }

    /**
     * @param ResidentHealthInsurance $insurance
     * @return null|string
     */
    public function getInsuranceSecondImage(ResidentHealthInsurance $insurance): ?string
    {
        $image = null;

        if ($insurance->getResident() && $insurance->getSecondFile() !== null) {
            if ($insurance->getSecondFile()->getMimeType() === 'application/pdf') {
                $second = $this->s3Service->downloadFile($insurance->getSecondFile()->getS3Id(), $insurance->getSecondFile()->getType());

                $img = new \Imagick();
                $img->setResolution(300, 300);
                $img->setCompression(\Imagick::COMPRESSION_JPEG);
                $img->setCompressionQuality(100);

                if ($second !== null) {
                    /** @var Stream $firstStream */
                    $secondStream = $second['Body'];

                    $img2 = new \Imagick();
                    $img2->setResolution(300, 300);
                    $img2->readImageBlob($secondStream->getContents());
                    while ($img2->hasPreviousImage()) {
                        $img2->removeImage();
                    }
                    $img->addImage($img2);
                }

                $random_name = '/tmp/hif_' . md5($insurance->getSecondFile()->getId()) . '_' . (new \DateTime())->format('Ymd_His') . '.jpeg';
                $img->setImageFormat('jpeg');
                $img->writeImage($random_name);
                $img->destroy();

                if (file_exists($random_name)) {
                    $dataObject = Data::buildFromFile($random_name);

                    $image = Dumper::dump($dataObject);
                }
            } else {
                $image = $this->s3Service->getFile($insurance->getSecondFile()->getS3Id(), $insurance->getSecondFile()->getType());
            }
        }

        return $image;
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
     * @return Profile
     */
    public function getProfileNoAdmissionReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId, $discontinued): Profile
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

        /** @var ResidentHealthInsuranceRepository $insuranceRepo */
        $insuranceRepo = $this->em->getRepository(ResidentHealthInsurance::class);
        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);
        /** @var ResidentMedicationAllergyRepository $medicationAllergenRepo */
        $medicationAllergenRepo = $this->em->getRepository(ResidentMedicationAllergy::class);
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

        $insurances = $insuranceRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $residentIds);
        $medications = $medicationRepo->getWithDiscontinuedByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);
        $medicationAllergens = $medicationAllergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $residentIds);
        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);
        $diagnosis = $diagnosisRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentIds);
        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
        $physicians = $physicianRepo->getByNoAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $residentIds);
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

        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(static function ($item) {
                return $item['pId'];
            }, $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
        }

        $insuranceArray = [];
        $insuranceFiles = [];
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

                if ($this->getInsuranceFirstImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceFirstImage($insurance);
                }

                if ($this->getInsuranceSecondImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceSecondImage($insurance);
                }
            }
        }

        $report = new Profile();
        $report->setResidents($residentsById);
        $report->setInsurances($insuranceArray);
        $report->setInsuranceFiles($insuranceFiles);
        $report->setMedications($medications);
        $report->setMedicationAllergens($medicationAllergens);
        $report->setAllergens($allergens);
        $report->setDiagnosis($diagnosis);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);
        $report->setPhysicians($physicians);
        $report->setPhysicianPhones($physicianPhones);
        $report->setEvents($events);
        $report->setRents($rents);
        $report->setDiscontinued($discontinued);
        $report->setImages($this->getResidentImages($residentIds));

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
     * @return FaceSheet
     */
    public function getFaceSheetReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): FaceSheet
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsFullInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, $residentId, $this->getNotGrantResidentIds());
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentHealthInsuranceRepository $insuranceRepo */
        $insuranceRepo = $this->em->getRepository(ResidentHealthInsurance::class);
        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);
        /** @var ResidentMedicationAllergyRepository $medicationAllergenRepo */
        $medicationAllergenRepo = $this->em->getRepository(ResidentMedicationAllergy::class);
        /** @var ResidentAllergenRepository $allergenRepo */
        $allergenRepo = $this->em->getRepository(ResidentAllergen::class);
        /** @var ResidentDiagnosisRepository $diagnosisRepo */
        $diagnosisRepo = $this->em->getRepository(ResidentDiagnosis::class);
        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $insurances = $insuranceRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $residentIds);
        $medications = $medicationRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);
        $medicationAllergens = $medicationAllergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $residentIds);
        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);
        $diagnosis = $diagnosisRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentIds);
        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
        $physicians = $physicianRepo->getByAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $type, $residentIds);

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

        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(static function ($item) {
                return $item['pId'];
            }, $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
        }

        $insuranceArray = [];
        $insuranceFiles = [];
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

                if ($this->getInsuranceFirstImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceFirstImage($insurance);
                }

                if ($this->getInsuranceSecondImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceSecondImage($insurance);
                }
            }
        }

        $report = new FaceSheet();
        $report->setResidents($residentsById);
        $report->setInsurances($insuranceArray);
        $report->setInsuranceFiles($insuranceFiles);
        $report->setMedications($medications);
        $report->setMedicationAllergens($medicationAllergens);
        $report->setAllergens($allergens);
        $report->setDiagnosis($diagnosis);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);
        $report->setPhysicians($physicians);
        $report->setPhysicianPhones($physicianPhones);
        $report->setImages($this->getResidentImages($residentIds));

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
     * @return FaceSheet
     */
    public function getFaceSheetNoAdmissionReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): FaceSheet
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

        /** @var ResidentHealthInsuranceRepository $insuranceRepo */
        $insuranceRepo = $this->em->getRepository(ResidentHealthInsurance::class);
        /** @var ResidentMedicationRepository $medicationRepo */
        $medicationRepo = $this->em->getRepository(ResidentMedication::class);
        /** @var ResidentMedicationAllergyRepository $medicationAllergenRepo */
        $medicationAllergenRepo = $this->em->getRepository(ResidentMedicationAllergy::class);
        /** @var ResidentAllergenRepository $allergenRepo */
        $allergenRepo = $this->em->getRepository(ResidentAllergen::class);
        /** @var ResidentDiagnosisRepository $diagnosisRepo */
        $diagnosisRepo = $this->em->getRepository(ResidentDiagnosis::class);
        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);
        /** @var ResidentPhysicianRepository $physicianRepo */
        $physicianRepo = $this->em->getRepository(ResidentPhysician::class);

        $insurances = $insuranceRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentHealthInsurance::class), $residentIds);
        $medications = $medicationRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentIds);
        $medicationAllergens = $medicationAllergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $residentIds);
        $allergens = $allergenRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAllergen::class), $residentIds);
        $diagnosis = $diagnosisRepo->getByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentIds);
        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
        $physicians = $physicianRepo->getByNoAdmissionResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentPhysician::class), $residentIds);

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

        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(static function ($item) {
                return $item['pId'];
            }, $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
        }

        $insuranceArray = [];
        $insuranceFiles = [];
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

                if ($this->getInsuranceFirstImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceFirstImage($insurance);
                }

                if ($this->getInsuranceSecondImage($insurance) !== null) {
                    $insuranceFiles[$insurance->getResident()->getId()][] = $this->getInsuranceSecondImage($insurance);
                }
            }
        }

        $report = new FaceSheet();
        $report->setResidents($residentsById);
        $report->setInsurances($insuranceArray);
        $report->setInsuranceFiles($insuranceFiles);
        $report->setMedications($medications);
        $report->setMedicationAllergens($medicationAllergens);
        $report->setAllergens($allergens);
        $report->setDiagnosis($diagnosis);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);
        $report->setPhysicians($physicians);
        $report->setPhysicianPhones($physicianPhones);
        $report->setImages($this->getResidentImages($residentIds));

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
     * @return ResidentDetailedRoster
     */
    public function getDetailedRosterReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ResidentDetailedRoster
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());

        $residentIds = [];
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

        $physicianPhones = [];
        if (!empty($physicians)) {
            $physicianIds = array_map(static function ($item) {
                return $item['pId'];
            }, $physicians);
            $physicianIds = array_unique($physicianIds);

            /** @var PhysicianPhoneRepository $physicianPhoneRepo */
            $physicianPhoneRepo = $this->em->getRepository(PhysicianPhone::class);

            $physicianPhones = $physicianPhoneRepo->getByPhysicianIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(PhysicianPhone::class), $physicianIds);
        }

        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);

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

        $report = new ResidentDetailedRoster();
        $report->setResidents($residentsById);
        $report->setPhysicians($physicians);
        $report->setPhysicianPhones($physicianPhones);
        $report->setResponsiblePersons($responsiblePersons);
        $report->setResponsiblePersonPhones($responsiblePersonPhones);

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
     * @return ResidentSimpleRoster
     */
    public function getSimpleRosterReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ResidentSimpleRoster
    {
        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());
        $typeIds = [];
        $numberOfFloors = [];

        $vacants = $this->getRoomVacancyList($group, $groupAll, $groupIds, $groupId, $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId);

        if (!empty($residents)) {
            $typeIds = array_map(static function ($item) {
                return $item['typeId'];
            }, $residents);
            $typeIds = array_unique($typeIds);

            if ($type === GroupType::TYPE_FACILITY) {
                $numberOfFloors = array_column($residents, 'numberOfFloors', 'typeId');
            }
        }

        $report = new ResidentSimpleRoster();
        $report->setResidents($residents);
        $report->setTypeIds($typeIds);
        $report->setNumberOfFloors($numberOfFloors);
        $report->setVacants($vacants);
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
     * @return DietaryRestriction
     */
    public function getDietaryRestrictionsReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): DietaryRestriction
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeId = $groupId;

        if (!\in_array($type, [GroupType::TYPE_FACILITY, GroupType::TYPE_REGION], false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionDietaryRestrictionsInfo($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());
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
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return SixtyDays
     */
    public function getSixtyDaysReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): SixtyDays
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

        $admissions = $admissionRepo->getResidents60DaysRosterData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $interval, $typeId, $this->getNotGrantResidentIds());

        $residentIds = [];

        if (!empty($admissions)) {
            $residentIds = array_map(static function ($item) {
                return $item['id'];
            }, $admissions);
            $residentIds = array_unique($residentIds);
        }

        /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
        $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);

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

                $rpAllArray = [];
                if (!empty($responsiblePersons)) {
                    /** @var ResidentResponsiblePerson $rp */
                    foreach ($responsiblePersons as $rp) {
                        $isEmergency = false;

                        if (!empty($rp->getRoles())) {
                            /** @var ResponsiblePersonRole $role */
                            foreach ($rp->getRoles() as $role) {
                                if ($role->isEmergency() === true) {
                                    $isEmergency = true;
                                    break;
                                }
                            }
                        }

                        $rpResidentId = $rp->getResident() ? $rp->getResident()->getId() : 0;
                        $rpId = $rp->getResponsiblePerson() ? $rp->getResponsiblePerson()->getId() : 0;

                        if ($isEmergency === true && $rpResidentId === $admission['id']) {

                            $rpArray = [
                                'rpId' => $rpId,
                                'rpFullName' => $rp->getResponsiblePerson() ? $rp->getResponsiblePerson()->getFirstName() . ' ' . $rp->getResponsiblePerson()->getLastName() : '',
                                'rpTitle' => $rp->getRelationship() ? $rp->getRelationship()->getTitle() : '',
                                'rpPhoneTitle' => 'N/A',
                                'rpPhoneNumber' => 'N/A',
                            ];

                            $rpPhone = [];
                            if (!empty($responsiblePersonPhones)) {
                                foreach ($responsiblePersonPhones as $phone) {
                                    if ($phone['rpId'] === $rpId) {
                                        $rpPhone = [
                                            'rpPhoneTitle' => $phone['type'],
                                            'rpPhoneNumber' => $phone['number'],
                                        ];

                                        if ((int)$phone['type'] === (int)\constant('App\\Model\\Phone::TYPE_EMERGENCY')) {
                                            $rpPhone = [
                                                'rpPhoneTitle' => $phone['type'],
                                                'rpPhoneNumber' => $phone['number'],
                                            ];
                                            break;
                                        }
                                    }
                                }
                            }
                            $rpAllArray = array_merge($rpArray, $rpPhone);
                        }
                    }
                }
                $data[] = array_merge($admissionArray, $rpAllArray);
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
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ReportResidentEvent
     */
    public function getEventReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ReportResidentEvent
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

        $residents = $repo->getAdmissionResidentsInfoByTypeOrId($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeId, null, $this->getNotGrantResidentIds());
        $residentIds = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[] = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        /** @var ResidentEventRepository $eventRepo */
        $eventRepo = $this->em->getRepository(ResidentEvent::class);

        $events = $eventRepo->getByResidentIdsAndDate($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $dateStart, $dateEnd, $residentIds);

        $report = new ReportResidentEvent();
        $report->setResidents($residentsById);
        $report->setEvents($events);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStartDate($dateStartFormatted);
        $report->setEndDate($dateEndFormatted);

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
     * @return ResidentMoveByMonth
     */
    public function getResidentMoveByMonthReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ResidentMoveByMonth
    {
        $currentSpace = $this->grantService->getCurrentSpace();

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
                'date' => $start->format('F') . ' ' . $start->format('y')
            ];

            $dateEndClone->modify('last day of previous month');
        }

        $interval = array_reverse($interval);

        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $finalData = [];
        $finalTotalDays = [];
        $finalGrandTotal = [];
        foreach ($interval as $subVal) {
            $subInterval = ImtDateTimeInterval::getWithDateTimes($subVal['subInterval']->getStart(), $subVal['subInterval']->getEnd());
            $rentPeriodFactory = clone RentPeriodFactory::getFactory($subVal['subInterval']);

            $admissions = $repo->getResidentMoveByMonthData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds(), null, true);

            $dischargedAdmissionEnds = null;
            foreach ($admissions as $admission) {
                if ($admission['admissionType'] === AdmissionType::DISCHARGE && $admission['discharged'] !== null) {
                    $dischargedAdmissionEnds[] = $admission['discharged']->format('Y-m-d H:i:s');
                }
            }

            $filteredAdmissions = $repo->getResidentMoveByMonthData($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $subInterval, $typeId, $this->getNotGrantResidentIds(), $dischargedAdmissionEnds, false);

            $modifiedAdmissions = [];
            $dischargedAdmissions = [];
            $minAdmitDates = [];
            foreach ($filteredAdmissions as $key => $admission) {
                if ($admission['admissionType'] === AdmissionType::DISCHARGE) {
                    if ($admission['id'] === $filteredAdmissions[$key - 1]['id']) {
                        $minAdmitDates[$admission['actionId']] = $filteredAdmissions[$key - 1]['admitted'];
                        $admission['original'] = $minAdmitDates[$admission['actionId']];
                    }

                    $dischargedAdmissions[] = $admission;
                } else {
                    $admission['original'] = null;
                }

                $modifiedAdmissions[] = $admission;
            }

            $admissionTypes = [
                AdmissionType::LONG_ADMIT,
                AdmissionType::SHORT_ADMIT,
                AdmissionType::DISCHARGE,
            ];

            $finalAdmissions = [];
            foreach ($modifiedAdmissions as $admission) {
                if (\in_array($admission['admissionType'], $admissionTypes, false) && $admission['admitted'] >= $subVal['subInterval']->getStart()) {
                    $finalAdmissions[] = $admission;
                }
            }

            $totalDays = [];
            foreach ($dischargedAdmissions as $admission) {
                $sumDays = 0;
                if (array_key_exists($admission['actionId'], $minAdmitDates) && $minAdmitDates[$admission['actionId']] !== null) {
                    $minAdmitDate = $minAdmitDates[$admission['actionId']];
                } else {
                    $minAdmitDate = $subInterval->getStart();
                }

                $calculationResults = $rentPeriodFactory->calculateForMoveReportInterval(
                    ImtDateTimeInterval::getWithDateTimes($minAdmitDate, $admission['admitted'])
                );

                $sumDays += $calculationResults['days'];
                $totalDays[$admission['actionId']] = $sumDays;
            }

            $types = array_column($finalAdmissions, 'typeShorthand', 'typeId');

            $totalByFacility = [];
            $grandTotal = [];
            foreach ($types as $typeId => $typeShorthand) {
                $sumLongTerm = 0;
                $sumShortTerm = 0;
                $sumMoveOut = 0;
                $totalLongTerm = 0;
                $totalShortTerm = 0;
                $totalMoveOut = 0;
                foreach ($finalAdmissions as $admission) {
                    $i = 0;
                    $j = 0;
                    $k = 0;
                    $x = 0;
                    $y = 0;
                    $z = 0;
                    if ($typeId === $admission['typeId']) {
                        if ($admission['admissionType'] === AdmissionType::LONG_ADMIT) {
                            $i++;

                            $sumLongTerm += $i;
                        }

                        if ($admission['admissionType'] === AdmissionType::SHORT_ADMIT) {
                            $j++;

                            $sumShortTerm += $j;
                        }

                        if ($admission['admissionType'] === AdmissionType::DISCHARGE) {
                            $k++;

                            $sumMoveOut += $k;
                        }
                    }

                    if ($admission['admissionType'] === AdmissionType::LONG_ADMIT) {
                        $x++;

                        $totalLongTerm += $x;
                    }

                    if ($admission['admissionType'] === AdmissionType::SHORT_ADMIT) {
                        $y++;

                        $totalShortTerm += $y;
                    }

                    if ($admission['admissionType'] === AdmissionType::DISCHARGE) {
                        $z++;

                        $totalMoveOut += $z;
                    }
                }

                $totalByFacility[$typeId] = [
                    'typeShorthand' => $typeShorthand . ' Total',
                    'sumLongTerm' => $sumLongTerm,
                    'sumShortTerm' => $sumShortTerm,
                    'sumMoveOut' => $sumMoveOut,
                ];

                $grandTotal = [
                    'sumLongTerm' => $totalLongTerm,
                    'sumShortTerm' => $totalShortTerm,
                    'sumMoveOut' => $totalMoveOut,
                ];
            }

            $data = array_merge($finalAdmissions, $totalByFacility);

            $typeShorthands = array_map(static function ($item) {
                return $item['typeShorthand'];
            }, $data);

            array_multisort($typeShorthands, SORT_ASC, $data);

            $finalTotalDays[$subVal['date']] = $totalDays;
            $finalGrandTotal[$subVal['date']] = $grandTotal;
            $finalData[$subVal['date']] = $data;
        }

        $report = new ResidentMoveByMonth();
        $report->setData($finalData);
        $report->setDays($finalTotalDays);
        $report->setGrandTotal($finalGrandTotal);
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
     * @return ResponsiblePersonEmails
     */
    public function getResponsiblePersonEmailsReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ResponsiblePersonEmails
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $type = $group;
        $typeIds = null;
        if (!empty($groupIds)) {
            $typeIds = !empty($groupIds[0]) ? $groupIds : [];
        }

        if (!\in_array($type, GroupType::getTypeValues(), false)) {
            throw new InvalidParameterException('group');
        }

        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        $residents = $repo->getAdmissionResidentsInfoByMultiTypeIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $type, $typeIds, $this->getNotGrantResidentIds());

        $data = [];
        if (!empty($residents)) {
            $residentIds = array_map(static function ($item) {
                return $item['id'];
            }, $residents);

            /** @var ResidentResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository(ResidentResponsiblePerson::class);

            $responsiblePersons = $responsiblePersonRepo->getResponsiblePersonByResidentIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentIds);
            $rpResidentIds = array_map(static function (ResidentResponsiblePerson $item) {
                return $item->getResident() ? $item->getResident()->getId() : 0;
            }, $responsiblePersons);

            foreach ($residents as $resident) {
                $room = '';
                if (array_key_exists('roomNumber', $resident)) {
                    if ($resident['private']) {
                        $room = $resident['roomNumber'];
                    } else {
                        $room = $resident['roomNumber'] . ' ' . $resident['bedNumber'];
                    }
                }

                if (!in_array($resident['id'], $rpResidentIds, false)) {
                    $data[] = [
                        'id' => $residentId,
                        'firstName' => $resident['firstName'],
                        'lastName' => $resident['lastName'],
                        'room' => $room,
                        'typeId' => $resident['typeId'],
                        'typeName' => $resident['typeName'],
                        'rpFirstName' => '',
                        'rpLastName' => '',
                        'rpId' => 0,
                        'rpAddress' => '',
                        'rpEmail' => '',
                        'rpSalutation' => '',
                    ];
                }

                if (!empty($responsiblePersons)) {
                    /** @var ResidentResponsiblePerson $responsiblePerson */
                    foreach ($responsiblePersons as $responsiblePerson) {
                        $residentId = $responsiblePerson->getResident() ? $responsiblePerson->getResident()->getId() : 0;

                        if ($residentId === $resident['id'] && $responsiblePerson->getResponsiblePerson() !== null) {
                            $data[] = [
                                'id' => $residentId,
                                'firstName' => $resident['firstName'],
                                'lastName' => $resident['lastName'],
                                'room' => $room,
                                'typeId' => $resident['typeId'],
                                'typeName' => $resident['typeName'],
                                'rpFirstName' => $responsiblePerson->getResponsiblePerson()->getFirstName(),
                                'rpLastName' => $responsiblePerson->getResponsiblePerson()->getLastName(),
                                'rpId' => $responsiblePerson->getResponsiblePerson()->getId(),
                                'rpAddress' => $responsiblePerson->getResponsiblePerson()->getAddress1(),
                                'rpEmail' => !empty($responsiblePerson->getResponsiblePerson()->getEmail()) ? $responsiblePerson->getResponsiblePerson()->getEmail() : 'N/A',
                                'rpSalutation' => $responsiblePerson->getResponsiblePerson()->getSalutation() ? $responsiblePerson->getResponsiblePerson()->getSalutation()->getTitle() : '',
                            ];
                        }
                    }
                }
            }
        }

        $report = new ResponsiblePersonEmails();
        $report->setData($data);
        $report->setStrategy(GroupType::getTypes()[$type]);
        $report->setStrategyId($type);

        return $report;
    }
}