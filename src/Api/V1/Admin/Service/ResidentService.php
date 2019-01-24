<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Helper\ResidentPhotoHelper;
use App\Api\V1\Common\Service\IGridService;
use App\Api\V1\Component\Rent\RentPeriodFactory;
use App\Entity\Allergen;
use App\Entity\Apartment;
use App\Entity\ApartmentBed;
use App\Entity\ApartmentRoom;
use App\Entity\ContractAction;
use App\Entity\Diagnosis;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityRoom;
use App\Entity\Medication;
use App\Entity\PaymentSource;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentEvent;
use App\Entity\ResidentPhone;
use App\Entity\ResidentRent;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonPhone;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Model\ContractType;
use App\Model\Report\Payor;
use App\Model\Report\RoomOccupancyRate;
use App\Model\Report\BloodPressureCharting;
use App\Model\Report\BowelMovement;
use App\Model\Report\ChangeoverNotes;
use App\Model\Report\DietaryRestriction;
use App\Model\Report\FaceSheet;
use App\Model\Report\Manicure;
use App\Model\Report\MealMonitor;
use App\Model\Report\MedicationChart;
use App\Model\Report\MedicationList;
use App\Model\Report\NightActivity;
use App\Model\Report\Profile;
use App\Model\Report\ResidentBirthdayList;
use App\Model\Report\ResidentDetailedRoster;
use App\Model\Report\ResidentSimpleRoster;
use App\Model\Report\RoomAudit;
use App\Model\Report\RoomRent;
use App\Model\Report\RoomRentMasterNew;
use App\Model\Report\RoomVacancyList;
use App\Model\Report\ShowerSkinInspection;
use App\Model\Report\SixtyDays;
use App\Util\Common\ImtDateTimeInterval;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Class ResidentService
 * @package App\Api\V1\Admin\Service
 */
class ResidentService extends BaseService implements IGridService
{
    /**
     * @var ResidentPhotoHelper
     */
    private $residentPhotoHelper;

    /**
     * @param ResidentPhotoHelper $residentPhotoHelper
     */
    public function setResidentPhotoHelper(ResidentPhotoHelper $residentPhotoHelper)
    {
        $this->residentPhotoHelper = $residentPhotoHelper;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Resident::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Resident::class)->findAll();
    }

    /**
     * @param $id
     * @return Facility|null|object
     */
    public function getById($id)
    {
        /**
         * @var Resident $resident
         */
        $resident = $this->em->getRepository(Resident::class)->find($id);

        if (is_null($resident)) {
            throw new ResidentNotFoundException();
        } else {
            $photo = $this->residentPhotoHelper->get($resident->getId());

            if (!empty($photo)) {
                $resident->setPhoto($photo);
            }
        }

        return $resident;
    }

    /**
     * @return Resident|null|object
     */
    public function getNoContractResidents()
    {
        return $this->em->getRepository(Resident::class)->getNoContractResidents();
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Space $space
             * @var Physician $physician
             * @var Salutation $salutation
             */
            $this->em->getConnection()->beginTransaction();

            $spaceId      = $params['space_id'] ?? 0;
            $salutationId = $params['salutation_id'] ?? 0;
            $space        = null;
            $physician    = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if (is_null($space)) {
                    throw new SpaceNotFoundException();
                }
            }

            if ($salutationId && $salutationId > 0) {
                $salutation = $this->em->getRepository(Salutation::class)->find($salutationId);

                if (is_null($salutation)) {
                    throw new SalutationNotFoundException();
                }
            }

            $resident = new Resident();
            $resident->setFirstName($params['first_name'] ?? '');
            $resident->setLastName($params['last_name'] ?? '');
            $resident->setMiddleName($params['middle_name'] ?? '');
            $resident->setSpace($space);
            $resident->setSalutation($salutation);
            $resident->setGender($params['gender'] ?? 0);
            $resident->setBirthday(new \DateTime($params['birthday']));
            $resident->setPhones($this->savePhones($resident, $params['phones'] ?? []));

            $this->validate($resident, null, ['api_admin_resident_add']);
            $this->em->persist($resident);

            // save photo
            if (!empty($params['photo'])) {
                $this->residentPhotoHelper->remove($resident->getId());
                $this->residentPhotoHelper->save($resident->getId(), $params['photo']);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit($id, array $params) : void
    {
        try {
            /**
             * @var Resident $resident
             * @var Space $space
             * @var Salutation $salutation
             * @var Physician $physician
             */
            $this->em->getConnection()->beginTransaction();

            $resident = $this->em->getRepository(Resident::class)->find($id);

            if (is_null($resident)) {
                throw new ResidentNotFoundException();
            }

            $spaceId      = $params['space_id'] ?? 0;
            $salutationId = $params['salutation_id'] ?? 0;
            $space        = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if (is_null($space)) {
                    throw new SpaceNotFoundException();
                }
            }

            if ($salutationId && $salutationId > 0) {
                $salutation = $this->em->getRepository(Salutation::class)->find($salutationId);

                if (is_null($salutation)) {
                    throw new SalutationNotFoundException();
                }
            }

            $resident->setFirstName($params['first_name'] ?? '');
            $resident->setLastName($params['last_name'] ?? '');
            $resident->setMiddleName($params['middle_name'] ?? '');
            $resident->setSpace($space);
            $resident->setSalutation($salutation);
            $resident->setGender($params['gender'] ?? 0);
            $resident->setBirthday(new \DateTime($params['birthday']));
            $resident->setPhones($this->savePhones($resident, $params['phones'] ?? []));

            $this->validate($resident, null, ['api_admin_resident_edit']);
            $this->em->persist($resident);

            // save photo
            if (!empty($params['photo'])) {
                $this->residentPhotoHelper->remove($resident->getId());
                $this->residentPhotoHelper->save($resident->getId(), $params['photo']);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $resident
     * @param array $phones
     * @return array
     */
    private function savePhones($resident, array $phones = [])
    {
        /**
         * @var ResidentPhone[] $oldPhones
         */
        $oldPhones = $this->em->getRepository(ResidentPhone::class)->findBy(['resident' => $resident]);

        foreach ($oldPhones as $phone) {
            $this->em->remove($phone);
        }

        $hasPrimary = false;

        $residentPhones = [];

        foreach($phones as $phone) {
            $residentPhone = new ResidentPhone();
            $residentPhone->setResident($resident);
            $residentPhone->setCompatibility($phone['compatibility'] ?? null);
            $residentPhone->setType($phone['type']);
            $residentPhone->setNumber($phone['number']);
            $residentPhone->setPrimary((bool) $phone['primary'] ?? false);
            $residentPhone->setSmsEnabled((bool) $phone['sms_enabled'] ?? false);

            if ($residentPhone->isPrimary()) {
                if ($hasPrimary) {
                    throw new PhoneSinglePrimaryException();
                }

                $hasPrimary = true;
            }

            $this->em->persist($residentPhone);

            $residentPhones[] = $residentPhone;
        }

        return $residentPhones;
    }

    /**
     * @param $id
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->find($id);

            if (is_null($resident)) {
                throw new ResidentNotFoundException();
            }

            $this->residentPhotoHelper->remove($resident->getId());

            $this->em->remove($resident);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new ResidentNotFoundException();
            }

            /** @var Resident $resident */
            $residents = $this->em->getRepository(Resident::class)->findByIds($ids);

            if (empty($residents)) {
                throw new ResidentNotFoundException();
            }

            $this->em->getConnection()->beginTransaction();

            foreach ($residents as $resident) {
                $this->residentPhotoHelper->remove($resident->getId());
                $this->em->remove($resident);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function photo($id, array $params) : void
    {
        try {
            /**
             * @var Resident $resident
             */
            $this->em->getConnection()->beginTransaction();

            $resident = $this->em->getRepository(Resident::class)->find($id);

            if (is_null($resident)) {
                throw new ResidentNotFoundException();
            }

            if (!empty($params['photo'])) {
                $this->residentPhotoHelper->save($resident->getId(), $params['photo']);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getReport(Request $request)
    {
        /** @todo add other reports about residents **/
        if ($request->get('alias') == 'residents-birthday-list') {
            return $this->getBirthdayListReport($request);
        } elseif ($request->get('alias') == 'blood-pressure-charting') {
            return $this->getBloodPressureChartingReport($request);
        } elseif ($request->get('alias') == 'bowel-movement') {
            return $this->getBowelMovementReport($request);
        } elseif ($request->get('alias') == 'manicure') {
            return $this->getManicureReport($request);
        } elseif ($request->get('alias') == 'changeover-notes') {
            return $this->getChangeoverNotesReport($request);
        } elseif ($request->get('alias') == 'meal-monitor') {
            return $this->getMealMonitorReport($request);
        } elseif ($request->get('alias') == 'dietary-restrictions') {
            return $this->getDietaryRestrictionsReport($request);
        } elseif ($request->get('alias') == 'night-activity') {
            return $this->getNightActivityReport($request);
        } elseif ($request->get('alias') == 'room-audit') {
            return $this->getRoomAuditReport($request);
        } elseif ($request->get('alias') == 'shower-skin-inspection') {
            return $this->getShowerSkinInspectionReport($request);
        } else {
            throw new ParameterNotFoundException('Invalid report');
        }
    }

    /**
     * @param Request $request
     * @return ResidentBirthdayList
     */
    public function getBirthdayListReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getByType($type, $typeId);

        $report = new ResidentBirthdayList();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return BloodPressureCharting
     */
    public function getBloodPressureChartingReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_APARTMENT])) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getContractInfoByType($type, $typeId);

        $report = new BloodPressureCharting();
        $report->setTitle('WEIGHT AND BLOOD PRESSURE CHART');
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return BowelMovement
     */
    public function getBowelMovementReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type_id');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getBowelMovementInfoByType($type, $typeId);

        $report = new BowelMovement();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return Manicure
     */
    public function getManicureReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type || !in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getManicureInfoByType($type, $typeId);

        $report = new Manicure();
        $report->setTitle('MANICURE REPORT');
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return ChangeoverNotes
     */
    public function getChangeoverNotesReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getChangeoverNotesInfo($type, $typeId);

        $report = new ChangeoverNotes();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return MealMonitor
     */
    public function getMealMonitorReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getMealMonitorInfo($type, $typeId);

        $report = new MealMonitor();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return DietaryRestriction
     */
    public function getDietaryRestrictionsReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getDietaryRestrictionsInfo($type, $typeId);

        $report = new DietaryRestriction();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return NightActivity
     */
    public function getNightActivityReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getNightActivityInfo($type, $typeId);

        $report = new NightActivity();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return RoomAudit
     */
    public function getRoomAuditReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getRoomAuditInfo($type, $typeId);

        $report = new RoomAudit();
        $report->setTitle('ROOM AUDIT REPORT');
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return ShowerSkinInspection
     */
    private function getShowerSkinInspectionReport(Request $request)
    {
        $all        = (bool) $request->get('all') ?? false;
        $type       = $request->get('type');
        $typeId     = $request->get('type_id') ?? false;
        $residentId = $request->get('resident_id') ?? false;

        if ($type && !in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$type && !$residentId) {
            throw new ParameterNotFoundException('type, resident_id');
        }

        if ($type && !$typeId && !$all) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getShowerSkinInspectionInfo($type, $typeId, $residentId);

        $report = new ShowerSkinInspection();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return MedicationList
     */
    public function getMedicationListReport(Request $request)
    {
        $all        = (bool) $request->get('all') ?? false;
        $type       = $request->get('type');
        $typeId     = $request->get('type_id') ?? false;
        $residentId = $request->get('resident_id') ?? false;

        if ($type && !in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        if (!$type && !$residentId) {
            throw new ParameterNotFoundException('type, resident_id');
        }

        if ($type && !$typeId && !$all) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents     = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, $typeId, $residentId);
        $residentIds   = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[]                  = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $medications = $this->em->getRepository(Medication::class)->getByResidentIds($residentIds);

        $report = new MedicationList();
        $report->setResidents($residentsById);
        $report->setMedications($medications);

        return $report;
    }

    /**
     * @param Request $request
     * @return MedicationChart
     */
    public function getMedicationChartReport(Request $request)
    {
        $type       = $request->get('type') ?? false;
        $residentId = $request->get('resident_id') ?? false;

        if ($type && !in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        $residents     = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, false, $residentId);
        $residentIds   = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[]                  = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $medications = $this->em->getRepository(Medication::class)->getByResidentIds($residentIds);
        $allergens   = $this->em->getRepository(Allergen::class)->getByResidentIds($residentIds);

        $report = new MedicationChart();
        $report->setResidents($residents);
        $report->setMedications($medications);
        $report->setAllergens($allergens);

        return $report;
    }

    /**
     * @param Request $request
     * @return ResidentSimpleRoster
     */
    public function getSimpleRosterReport(Request $request)
    {
        $type       = $request->get('type') ?? false;
        $residentId = $request->get('resident_id') ?? false;

        if ($type && !in_array($type, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type');
        }

        $residents     = $this->em->getRepository(Resident::class)->getResidentsInfoByTypeOrId($type, false, $residentId);
        $residentIds   = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[]                  = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $report = new ResidentSimpleRoster();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return ResidentDetailedRoster
     */
    public function getDetailedRosterReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, \App\Model\Resident::getTypeValues())) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $residents = $this->em->getRepository(Resident::class)->getResidentDetailedInfo($type, $typeId);

        $residentIds   = [];
        $residentsById = [];

        foreach ($residents as $resident) {
            $residentIds[]                  = $resident['id'];
            $residentsById[$resident['id']] = $resident;
        }

        $responsiblePersons = $this->em->getRepository(ResponsiblePerson::class)->getByResidentIds($residentIds);

        $report = new ResidentDetailedRoster();
        $report->setResidents($residents);
        $report->setResponsiblePersons($responsiblePersons);

        return $report;
    }

    /**
     * @param Request $request
     * @return \App\Model\Report\ResidentEvent
     */
    public function getEventReport(Request $request)
    {
        $type       = $request->get('type');
        $typeId     = $request->get('type_id') ?? false;
        $startDate  = $request->get('start_date');
        $endDate    = $request->get('end_date');

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, \App\Model\Resident::getTypeValues())) {
            throw new InvalidParameterException('type');
        }

        list($m1, $d1, $y1) = explode('/', $startDate);
        list($m2, $d2, $y2) = explode('/', $endDate);

        if (!checkdate($m1, $d1, $y1) || !checkdate($m2, $d2, $y2)) {
            throw new InvalidParameterException('start_date, end_date');
        }

        $startDate = \DateTime::createFromFormat('m/d/Y', $startDate);
        $endDate   = \DateTime::createFromFormat('m/d/Y', $endDate);

        $events = $this->em->getRepository(ResidentEvent::class)->getByPeriodAndType($startDate, $endDate, $type, $typeId);

        $report = new \App\Model\Report\ResidentEvent();
        $report->setEvents($events);

        return $report;
    }

    /**
     * @param Request $request
     * @return SixtyDays
     * @throws \Exception
     */
    public function getSixtyDaysReport(Request $request)
    {
        $type   = $request->get('type');
        $typeId = $request->get('type_id') ?? false;
        $date   = $request->get('date');

        if (!$type) {
            throw new ParameterNotFoundException('type');
        }

        if (!in_array($type, \App\Model\Resident::getTypeValues())) {
            throw new InvalidParameterException('type');
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
     * @param Request $request
     * @return FaceSheet
     */
    public function getFaceSheetReport(Request $request)
    {
        $all = $request->get('all') ? (bool)$request->get('all') : false;
        $type = $request->get('type');
        $typeId = $request->get('type_id') ?? false;
        $residentId = $request->get('resident_id') ?? false;

        if (!$type || ($type && !\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false))) {
            throw new InvalidParameterException('type');
        }

        if (!$type && !$residentId) {
            throw new ParameterNotFoundException('type, resident_id');
        }

        if ($type && !$typeId && !$all) {
            throw new ParameterNotFoundException('type_id, all');
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
     * @param Request $request
     * @return Profile
     */
    public function getProfileReport(Request $request)
    {
        $all = $request->get('all') ? (bool)$request->get('all') : false;
        $type = $request->get('type');
        $typeId = $request->get('type_id') ?? false;
        $residentId = $request->get('resident_id') ?? false;

        if (!$type || ($type && !\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_REGION], false))) {
            throw new InvalidParameterException('type');
        }

        if (!$type && !$residentId) {
            throw new ParameterNotFoundException('type, resident_id');
        }

        if ($type && !$typeId && !$all) {
            throw new ParameterNotFoundException('type_id, all');
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
     * @param Request $request
     * @return RoomOccupancyRate
     */
    public function getRoomOccupancyRateReport(Request $request)
    {
        $all = $request->get('all') ? (bool)$request->get('all') : false;
        $type = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type || ($type && !\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_APARTMENT], false))) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $rooms = [];
        $types = [];
        $data = [];

        if ((int)$type === ContractType::TYPE_FACILITY) {
            if ($typeId) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->findBy(['facility' => $typeId]);
                $types = $this->em->getRepository(Facility::class)->findBy(['id' => $typeId]);
            }

            if ($all) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->findAll();
                $types = $this->em->getRepository(Facility::class)->orderedFindAll();
            }

            $bedIds = [];
            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                $facilityBeds = $this->em->getRepository(FacilityBed::class)->getBedIdAndTypeIdByRooms($roomIds);

                $ids = [];
                if (\count($facilityBeds)) {
                    $ids = array_map(function($item){return $item['id'];} , $facilityBeds);
                    $bedIds = array_column($facilityBeds, 'typeId', 'id');
                    $bedIds = array_count_values($bedIds);
                }

                $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId(ContractType::TYPE_FACILITY, $ids);

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
        } elseif ((int)$type === ContractType::TYPE_APARTMENT) {
            if ($typeId) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->findBy(['apartment' => $typeId]);
                $types = $this->em->getRepository(Apartment::class)->findBy(['id' => $typeId]);
            }

            if ($all) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->findAll();
                $types = $this->em->getRepository(Apartment::class)->orderedFindAll();
            }

            $bedIds = [];
            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                $apartmentBeds = $this->em->getRepository(ApartmentBed::class)->getBedIdAndTypeIdByRooms($roomIds);

                $ids = [];
                if (\count($apartmentBeds)) {
                    $ids = array_map(function($item){return $item['id'];} , $apartmentBeds);
                    $bedIds = array_column($apartmentBeds, 'typeId', 'id');
                    $bedIds = array_count_values($bedIds);
                }

                $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId(ContractType::TYPE_APARTMENT, $ids);

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
        $report->setStrategy(ContractType::getTypes()[(int)$type]);

        return $report;
    }

    /**
     * @param Request $request
     * @return RoomVacancyList
     */
    public function getRoomVacancyListReport(Request $request)
    {
        $all = $request->get('all') ? (bool)$request->get('all') : false;
        $type = $request->get('type');
        $typeId = $request->get('type_id') ?? false;

        if (!$type || ($type && !\in_array($type, [ContractType::TYPE_FACILITY, ContractType::TYPE_APARTMENT], false))) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $rooms = [];
        $data = [];

        if ((int)$type === ContractType::TYPE_FACILITY) {
            if ($typeId) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->findBy(['facility' => $typeId]);
            }

            if ($all) {
                $rooms = $this->em->getRepository(FacilityRoom::class)->findAll();
            }

            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (FacilityRoom $item) {
                    return $item->getId();
                }, $rooms);

                $facilityBeds = $this->em->getRepository(FacilityBed::class)->getBedIdAndTypeIdByRooms($roomIds);

                if (\count($facilityBeds)) {
                    $bedIds = array_map(function($item){return $item['id'];} , $facilityBeds);

                    $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId(ContractType::TYPE_FACILITY, $bedIds);

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
        } elseif ((int)$type === ContractType::TYPE_APARTMENT) {
            if ($typeId) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->findBy(['apartment' => $typeId]);
            }

            if ($all) {
                $rooms = $this->em->getRepository(ApartmentRoom::class)->findAll();
            }

            $occupancyBedIds = [];
            if (!empty($rooms)) {

                $roomIds = array_map(function (ApartmentRoom $item) {
                    return $item->getId();
                }, $rooms);

                $apartmentBeds = $this->em->getRepository(ApartmentBed::class)->getBedIdAndTypeIdByRooms($roomIds);

                if (\count($apartmentBeds)) {
                    $bedIds = array_map(function($item){return $item['id'];} , $apartmentBeds);

                    $contractActions = $this->em->getRepository(ContractAction::class)->getBedIdAndTypeId(ContractType::TYPE_APARTMENT, $bedIds);

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
        $report->setStrategy(ContractType::getTypes()[(int)$type]);

        return $report;
    }

    /**
     * @param Request $request
     * @return Payor
     * @throws \Exception
     */
    public function getPayorReport(Request $request)
    {
        $all = $request->get('all') ? (bool)$request->get('all') : false;
        $type = $request->get('type');
        $typeId = $request->get('type_id') ?? false;
        $date = $request->get('date');

        if (!$type || ($type && !\in_array($type, ContractType::getTypeValues(), false))) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $reportDate = new \DateTime('now');
        $reportDateFormatted = $reportDate->format('M/Y');

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
            $reportDateFormatted = $reportDate->format('M/Y');
        }

        $interval = ImtDateTimeInterval::getWithMonthAndYear($reportDate->format('Y'), $reportDate->format('m'));

        $data = $this->em->getRepository(ResidentRent::class)->getRentsWithSources((int)$type, $interval, $typeId);
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

        $sources = $this->em->getRepository(PaymentSource::class)->getPaymentSources();

        $report = new Payor();
        $report->setData($data);
        $report->setCalcAmount($calcAmount);
        $report->setPlace($place);
        $report->setTotal($total);
        $report->setSources($sources);
        $report->setStrategy(ContractType::getTypes()[(int)$type]);
        $report->setStrategyId((int)$type);
        $report->setDate($reportDateFormatted);

        return $report;
    }

    /**
     * @param Request $request
     * @return RoomRentMasterNew
     * @throws \Exception
     */
    public function getRoomRentMasterNewReport(Request $request)
    {
        $all = $request->get('all') ? (bool)$request->get('all') : false;
        $type = $request->get('type');
        $typeId = $request->get('type_id') ?? false;
        $date = $request->get('date');

        if (!$type || ($type && !\in_array($type, ContractType::getTypeValues(), false))) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
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
                    $types = $this->em->getRepository(Facility::class)->findBy(['id' => $typeId]);
                }

                if ($all) {
                    $types = $this->em->getRepository(Facility::class)->orderedFindAll();
                }

                break;
            case ContractType::TYPE_APARTMENT:
                if ($typeId) {
                    $types = $this->em->getRepository(Apartment::class)->findBy(['id' => $typeId]);
                }

                if ($all) {
                    $types = $this->em->getRepository(Apartment::class)->orderedFindAll();
                }

                break;
            case ContractType::TYPE_REGION:
                if ($typeId) {
                    $types = $this->em->getRepository(Region::class)->findBy(['id' => $typeId]);
                }

                if ($all) {
                    $types = $this->em->getRepository(Region::class)->orderedFindAll();
                }

                break;
            default:
                throw new IncorrectStrategyTypeException();
        }

        $rents = $this->em->getRepository(ResidentRent::class)->getRoomRentMasterNewData((int)$type, $subInterval, $typeId);
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);
        $data = [];

        if ((int)$type !== ContractType::TYPE_REGION) {
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

                if ((int)$type !== ContractType::TYPE_REGION) {
                    $data[$typeId]['occupancy'] = \count($data[$typeId]['occupancies']) === 0 ? 0 : array_sum($data[$typeId]['occupancies']) / \count($data[$typeId]['occupancies']);
                    $data[$typeId]['occupancy'] = number_format($data[$typeId]['occupancy'] * 100, 2);
                    $data[$typeId]['occupancy'] = $data[$typeId]['occupancy'] > 100 ? 100 : $data[$typeId]['occupancy'];

                    $occupancyRate = $this->getRoomOccupancyRateReport($request);

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
        $report->setStrategy(ContractType::getTypes()[(int)$type]);
        $report->setStrategyId((int)$type);
        $report->setDateStart($dateStart);
        $report->setDateEnd($dateEnd);

        return $report;
    }

    /**
     * @param Request $request
     * @return RoomRent
     * @throws \Exception
     */
    public function getRoomRentReport(Request $request)
    {
        $all = $request->get('all') ? (bool)$request->get('all') : false;
        $type = $request->get('type');
        $typeId = $request->get('type_id') ?? false;
        $date = $request->get('date');

        if (!$type || ($type && !\in_array($type, ContractType::getTypeValues(), false))) {
            throw new InvalidParameterException('type');
        }

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        $now = new \DateTime('now');
        $reportDate = $now;

        if (!empty($date)) {
            $reportDate = new \DateTime($date);
        }

        $subInterval = ImtDateTimeInterval::getDateDiffForMonthAndYear($reportDate->format('Y'), $reportDate->format('m'));

        $dateStart = $subInterval->getStart()->format('m/d/Y');
        $dateEnd = $subInterval->getEnd()->format('m/d/Y');

        $data = $this->em->getRepository(ResidentRent::class)->getRoomRentData((int)$type, $subInterval, $typeId);
        $rentPeriodFactory = RentPeriodFactory::getFactory($subInterval);

        $residentIds = array_map(function($item){return $item['id'];} , $data);
        $residentIds = array_unique($residentIds);
        $responsiblePersons = $this->em->getRepository(ResponsiblePerson::class)->getByResidentIds($residentIds);

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
        $report->setStrategy(ContractType::getTypes()[(int)$type]);
        $report->setStrategyId((int)$type);
        $report->setDateStart($dateStart);
        $report->setDateEnd($dateEnd);

        return $report;
    }
}
