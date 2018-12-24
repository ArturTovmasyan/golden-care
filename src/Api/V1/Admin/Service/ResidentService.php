<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Helper\ResidentPhotoHelper;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Facility;
use App\Entity\FacilityRoom;
use App\Entity\Medication;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentPhone;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Model\Report\BloodPressureCharting;
use App\Model\Report\BowelMovement;
use App\Model\Report\ChangeoverNotes;
use App\Model\Report\DietaryRestriction;
use App\Model\Report\Manicure;
use App\Model\Report\MealMonitor;
use App\Model\Report\MedicationChart;
use App\Model\Report\MedicationList;
use App\Model\Report\NightActivity;
use App\Model\Report\ResidentBirthdayList;
use App\Model\Report\RoomAudit;
use App\Model\Report\ShowerSkinInspection;
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
            $resident->setType($params['type'] ?? 0);
            $resident->setSpace($space);
            $resident->setSalutation($salutation);
            $resident->setGender($params['gender'] ?? 0);
            $resident->setBirthday(new \DateTime($params['birthday']));

            $this->validate($resident, null, ['api_admin_resident_add']);
            $this->em->persist($resident);

            // save phone numbers
            $this->savePhones($resident, $params['phones'] ?? []);
            $this->em->flush();

            // save photo
            if (!empty($params['photo'])) {
                $this->residentPhotoHelper->save($resident->getId(), $params['photo']);
            }

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

            $this->validate($resident, null, ['api_admin_resident_edit']);
            $this->em->persist($resident);

            // save photo
            if (!empty($params['photo'])) {
                $this->residentPhotoHelper->save($resident->getId(), $params['photo']);
            }

            // save phone numbers
            $this->savePhones($resident, $params['phones'] ?? []);

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
     * @return mixed
     * @throws \ReflectionException
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

            $this->validate($residentPhone, null, ['api_admin_resident_edit']);
            $this->em->persist($residentPhone);
        }
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

        try {
            $residents = $this->em->getRepository(Resident::class)->getMealMonitorInfo($type, $typeId);
        } catch (\Exception $e) {
            $residents = [];
        }

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

        try {
            $residents = $this->em->getRepository(Resident::class)->getDietaryRestrictionsInfo($type, $typeId);
        } catch (\Exception $e) {
            $residents = [];
        }

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

        try {
            $residents = $this->em->getRepository(Resident::class)->getNightActivityInfo($type, $typeId);
        } catch (\Exception $e) {
            $residents = [];
        }

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

        try {
            $residents = $this->em->getRepository(Resident::class)->getRoomAuditInfo($type, $typeId);
        } catch (\Exception $e) {
            $residents = [];
        }

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

        try {
            $residents = $this->em->getRepository(Resident::class)->getShowerSkinInspectionInfo($type, $typeId, $residentId);
        } catch (\Exception $e) {
            $residents = [];
        }

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

        //dump($report->getResidents());
        //dump($report->getMedications());exit;

        return $report;
    }
}
