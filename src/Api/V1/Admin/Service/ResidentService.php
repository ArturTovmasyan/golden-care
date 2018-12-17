<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\DiningRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\PhoneSinglePrimaryException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Helper\ResidentPhotoHelper;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ApartmentRoom;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\DiningRoom;
use App\Entity\Facility;
use App\Entity\FacilityRoom;
use App\Entity\Physician;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentApartmentOption;
use App\Entity\ResidentFacilityOption;
use App\Entity\ResidentPhone;
use App\Entity\ResidentRegionOption;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Model\Report\BloodPressureCharting;
use App\Model\Report\BowelMovement;
use App\Model\Report\ChangeoverNotes;
use App\Model\Report\DietaryRestriction;
use App\Model\Report\Manicure;
use App\Model\Report\MealMonitor;
use App\Model\Report\ResidentBirthdayList;
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
     * @param $type
     * @param $id
     * @param $state
     * @return mixed
     */
    public function getByTypeAndState($type, $id, $state)
    {
        return $this->em->getRepository(Resident::class)->getByTypeAndState($type, $id, $state);
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

            // save photo
            if (!empty($params['photo'])) {
                $this->residentPhotoHelper->save($resident->getId(), $params['photo']);
            }

            // save option
            switch ($resident->getType()) {
                case \App\Model\Resident::TYPE_APARTMENT:
                    $option = $this->saveApartmentOption($resident, $params['option']);
                    break;
                case \App\Model\Resident::TYPE_REGION:
                    $option = $this->saveRegionOption($resident, $params['option']);
                    break;
                default:
                    $option = $this->saveFacilityOption($resident, $params['option']);
            }

            $this->validate($option, null, ['api_admin_resident_add']);
            $this->em->persist($option);

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

            // save option
            switch ($resident->getType()) {
                case \App\Model\Resident::TYPE_APARTMENT:
                    $option = $this->saveApartmentOption($resident, $params['option']);
                    break;
                case \App\Model\Resident::TYPE_REGION:
                    $option = $this->saveRegionOption($resident, $params['option']);
                    break;
                default:
                    $option = $this->saveFacilityOption($resident, $params['option']);
            }

            $this->validate($option, null, ['api_admin_resident_edit']);
            $this->em->persist($option);

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
     * @param Resident $resident
     * @param array $params
     * @return ResidentFacilityOption|null|object
     */
    private function saveFacilityOption(Resident $resident, array $params)
    {
        /**
         * @var ResidentFacilityOption $option
         * @var DiningRoom $diningRoom
         * @var FacilityRoom $facilityRoom
         * @var CareLevel $careLevel
         */
        $option = $this->em->getRepository(ResidentFacilityOption::class)->findOneBy(['resident' => $resident]);

        if (!isset($params['dining_room_id']) || !$params['dining_room_id']) {
            throw new DiningRoomNotFoundException();
        }

        if (!isset($params['room_id']) || !$params['room_id']) {
            throw new FacilityRoomNotFoundException();
        }

        if (!isset($params['care_level_id']) || !$params['care_level_id']) {
            throw new CareLevelNotFoundException();
        }

        $diningRoom   = $this->em->getRepository(DiningRoom::class)->find($params['dining_room_id']);
        $facilityRoom = $this->em->getRepository(FacilityRoom::class)->find($params['room_id']);
        $careLevel    = $this->em->getRepository(CareLevel::class)->find($params['care_level_id']);

        if (is_null($diningRoom)) {
            throw new DiningRoomNotFoundException();
        }

        if (is_null($careLevel)) {
            throw new CareLevelNotFoundException();
        }

        if (is_null($facilityRoom)) {
            throw new FacilityRoomNotFoundException();
        }

        if (is_null($option)) {
            $option = new ResidentFacilityOption();
            $option->setResident($resident);
            $option->setState(\App\Model\Resident::ACTIVE);
            $option->setDateAdmitted(new \DateTime($params['date_admitted']));
        }

        $option->setDiningRoom($diningRoom);
        $option->setFacilityRoom($facilityRoom);
        $option->setDnr($params['dnr'] ?? false);
        $option->setPolst($params['polst'] ?? false);
        $option->setAmbulatory($params['ambulatory'] ?? false);
        $option->setCareGroup($params['care_group'] ?? '');
        $option->setCareLevel($careLevel);

        return $option;
    }

    /**
     * @param Resident $resident
     * @param array $params
     * @return ResidentApartmentOption|null|object
     */
    private function saveApartmentOption(Resident $resident, array $params)
    {
        /**
         * @var ResidentApartmentOption $option
         * @var ApartmentRoom $apartmentRoom
         */
        $option = $this->em->getRepository(ResidentApartmentOption::class)->findOneBy(['resident' => $resident]);

        if (!isset($params['room_id']) || !$params['room_id']) {
            throw new ApartmentRoomNotFoundException();
        }

        $apartmentRoom = $this->em->getRepository(ApartmentRoom::class)->find($params['room_id']);

        if (is_null($option)) {
            $option = new ResidentApartmentOption();
            $option->setResident($resident);
            $option->setState(\App\Model\Resident::ACTIVE);
            $option->setDateAdmitted(new \DateTime($params['date_admitted']));
        }

        $option->setApartmentRoom($apartmentRoom);

        return $option;
    }

    /**
     * @param Resident $resident
     * @param array $params
     * @return ResidentFacilityOption|ResidentRegionOption|null|object
     */
    private function saveRegionOption(Resident $resident, array $params)
    {
        /**
         * @var ResidentFacilityOption $option
         * @var Region $region
         * @var CityStateZip $csz
         * @var CareLevel $careLevel
         */
        $option = $this->em->getRepository(ResidentRegionOption::class)->findOneBy(['resident' => $resident]);

        if (!isset($params['region_id']) || !$params['region_id']) {
            throw new RegionNotFoundException();
        }

        if (!isset($params['csz_id']) || !$params['csz_id']) {
            throw new CityStateZipNotFoundException();
        }

        if (!isset($params['care_level']) || !$params['care_level']) {
            throw new CareLevelNotFoundException();
        }

        $region    = $this->em->getRepository(Region::class)->find($params['region_id']);
        $csz       = $this->em->getRepository(CityStateZip::class)->find($params['csz_id']);
        $careLevel = $this->em->getRepository(CareLevel::class)->find($params['care_level']);

        if (is_null($region)) {
            throw new RegionNotFoundException();
        }

        if (is_null($csz)) {
            throw new CityStateZipNotFoundException();
        }

        if (is_null($careLevel)) {
            throw new CareLevelNotFoundException();
        }

        if (is_null($option)) {
            $option = new ResidentRegionOption();
            $option->setResident($resident);
            $option->setState(\App\Model\Resident::ACTIVE);
            $option->setDateAdmitted(new \DateTime($params['date_admitted']));
        }

        $option->setRegion($region);
        $option->setCsz($csz);
        $option->setStreetAddress($params['street_address']);
        $option->setDnr($params['dnr'] ?? false);
        $option->setPolst($params['polst'] ?? false);
        $option->setAmbulatory($params['ambulatory'] ?? false);
        $option->setCareGroup($params['care_group'] ?? '');
        $option->setCareLevel($careLevel);

        return $option;
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
        } else {
            throw new ParameterNotFoundException('Invalid report');
        }
    }

    /**
     * @param Request $request
     * @return ResidentBirthdayList
     */
    private function getBirthdayListReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id');

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        try {
            if ($type && in_array($type, \App\Model\Resident::getTypeValues())) {
                $residents = $this->em->getRepository(Resident::class)->getByType($type, $typeId);
            } else {
                $residents = $this->em->getRepository(Resident::class)->findAll();
            }
        } catch (\Exception $e) {
            $residents = [];
        }

        $report = new ResidentBirthdayList();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return BloodPressureCharting
     */
    private function getBloodPressureChartingReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id');

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        if ($typeId && !in_array($typeId, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_APARTMENT])) {
            throw new InvalidParameterException('type_id');
        }

        try {
            if ($type && in_array($type, \App\Model\Resident::getTypeValues())) {
                $residents = $this->em->getRepository(Resident::class)->getContractInfoByType($type, $typeId);
            } else {
                $residents = $this->em->getRepository(Resident::class)->getContractInfo();
            }
        } catch (\Exception $e) {
            $residents = [];
        }

        $report = new BloodPressureCharting();
        $report->setTitle('WEIGHT AND BLOOD PRESSURE CHART');
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return BowelMovement
     */
    private function getBowelMovementReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id');

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        if ($typeId && !in_array($typeId, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type_id');
        }

        try {
            if ($type && in_array($type, \App\Model\Resident::getTypeValues())) {
                $residents = $this->em->getRepository(Resident::class)->getBowelMovementInfoByType($type, $typeId);
            } else {
                $residents = $this->em->getRepository(Resident::class)->getBowelMovementInfo();
            }
        } catch (\Exception $e) {
            $residents = [];
        }

        $report = new BowelMovement();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return Manicure
     */
    private function getManicureReport(Request $request)
    {
        $all    = (bool) $request->get('all') ?? false;
        $type   = $request->get('type');
        $typeId = $request->get('type_id');

        if (!$all && !$typeId) {
            throw new ParameterNotFoundException('type_id, all');
        }

        if ($typeId && !in_array($typeId, [\App\Model\Resident::TYPE_FACILITY, \App\Model\Resident::TYPE_REGION])) {
            throw new InvalidParameterException('type_id');
        }

        try {
            if ($type && in_array($type, \App\Model\Resident::getTypeValues())) {
                $residents = $this->em->getRepository(Resident::class)->getManicureInfoByType($type, $typeId);
            } else {
                $residents = $this->em->getRepository(Resident::class)->getManicureInfo();
            }
        } catch (\Exception $e) {
            $residents = [];
        }

        $report = new Manicure();
        $report->setTitle('MANICURE REPORT');
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return ChangeoverNotes
     */
    private function getChangeoverNotesReport(Request $request)
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
            $residents = $this->em->getRepository(Resident::class)->getChangeoverNotesInfo($type, $typeId);
        } catch (\Exception $e) {
            $residents = [];
        }

        $report = new ChangeoverNotes();
        $report->setResidents($residents);

        return $report;
    }

    /**
     * @param Request $request
     * @return MealMonitor
     */
    private function getMealMonitorReport(Request $request)
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
    private function getDietaryRestrictionsReport(Request $request)
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
}
