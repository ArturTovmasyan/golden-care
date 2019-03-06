<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentBedNotFoundException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\DiningRoomNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityBedNotFoundException;
use App\Api\V1\Common\Service\Exception\IncorrectStrategyTypeException;
use App\Api\V1\Common\Service\Exception\RegionCanNotHaveBedException;
use App\Api\V1\Common\Service\Exception\RegionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentAdmissionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ApartmentBed;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\DiningRoom;
use App\Entity\FacilityBed;
use App\Entity\Region;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Model\AdmissionType;
use App\Model\GroupType;
use App\Repository\ApartmentBedRepository;
use App\Repository\CareLevelRepository;
use App\Repository\CityStateZipRepository;
use App\Repository\DiningRoomRepository;
use App\Repository\FacilityBedRepository;
use App\Repository\RegionRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentAdmissionService
 * @package App\Api\V1\Admin\Service
 */
class ResidentAdmissionService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('ra.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getById($id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);
    }

    /**
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getActiveByResidentId($id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getActiveByResident($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);
    }

    /**
     * @param $type
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getActiveResidentsByStrategy($type, $id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getActiveResidentsByStrategy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $type, $id);
    }

    /**
     * @param $type
     * @param $id
     * @return ResidentAdmission|null|object
     */
    public function getInactiveResidentsByStrategy($type, $id)
    {
        /** @var ResidentAdmissionRepository $repo */
        $repo = $this->em->getRepository(ResidentAdmission::class);

        return $repo->getInactiveResidentsByStrategy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $type, $id);
    }

    /**
     * @return Resident|null|object
     */
    public function getNoAdmissionResidents()
    {
        /** @var ResidentRepository $repo */
        $repo = $this->em->getRepository(Resident::class);

        return $repo->getNoAdmissionResidents($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Resident::class));
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $type = $params['group_type'] ? (int)$params['group_type'] : 0;
            $admissionType = isset($params['admission_type']) ? (int)$params['admission_type'] : 0;

            $entity = new ResidentAdmission();
            $entity->setResident($resident);
            $entity->setGroupType($type);
            $entity->setAdmissionType($admissionType);
            $entity->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $addMode = true;

            switch ($entity->getGroupType()) {
                case GroupType::TYPE_FACILITY:
                    $validationGroup = 'api_admin_facility_add';
                    $entity = $this->saveAsFacility($entity, $params, $addMode);
                    break;
                case GroupType::TYPE_APARTMENT:
                    $validationGroup = 'api_admin_apartment_add';
                    $entity = $this->saveAsApartment($entity, $params, $addMode);
                    break;
                case GroupType::TYPE_REGION:
                    $validationGroup = 'api_admin_region_add';
                    $entity = $this->saveAsRegion($entity, $params, $addMode);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

            $this->validate($entity, null, [$validationGroup]);
            $this->em->persist($entity);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $entity->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);

            if ($entity === null) {
                throw new ResidentAdmissionNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $admissionType = isset($params['admission_type']) ? (int)$params['admission_type'] : 0;

            $entity->setResident($resident);
            $entity->setAdmissionType($admissionType);
            $entity->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $addMode = false;

            switch ($entity->getGroupType()) {
                case GroupType::TYPE_FACILITY:
                    $validationGroup = 'api_admin_facility_edit';
                    $entity = $this->saveAsFacility($entity, $params, $addMode);
                    break;
                case GroupType::TYPE_APARTMENT:
                    $validationGroup = 'api_admin_apartment_edit';
                    $entity = $this->saveAsApartment($entity, $params, $addMode);
                    break;
                case GroupType::TYPE_REGION:
                    $validationGroup = 'api_admin_region_edit';
                    $entity = $this->saveAsRegion($entity, $params, $addMode);
                    break;
                default:
                    throw new IncorrectStrategyTypeException();
            }

            $this->validate($entity, null, [$validationGroup]);
            $this->em->persist($entity);

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
     * @throws \Exception
     */
    public function move($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $id);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $type = !empty($params['group_type']) ? (int)$params['group_type'] : 0;

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            //assignment mode
            if (!empty($params['move_id'])) {

                /** @var ResidentAdmission $admission */
                $admission = $repo->getDataByResident($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $type, $id);

                if ($admission === null) {
                    throw new ResidentAdmissionNotFoundException();
                }

                $moveId = (int)$params['move_id'];

                switch ($type) {
                    case GroupType::TYPE_FACILITY:
                        /** @var FacilityBedRepository $facilityBedRepo */
                        $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

                        /** @var FacilityBed $bed */
                        $bed = $facilityBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $moveId);

                        if ($bed === null) {
                            throw new FacilityBedNotFoundException();
                        }

                        $now = new \DateTime('now');

                        $entity = new ResidentAdmission();
                        $entity->setResident($admission->getResident());
                        $entity->setGroupType($admission->getGroupType());
                        $entity->setAdmissionType(AdmissionType::READMIT);
                        $entity->setStart($now);
                        $entity->setDate($now);
                        $entity->setFacilityBed($bed);
                        $entity->setDiningRoom($admission->getDiningRoom());
                        $entity->setDnr($admission->isDnr());
                        $entity->setPolst($admission->isPolst());
                        $entity->setAmbulatory($admission->isAmbulatory());
                        $entity->setCareGroup($admission->getCareGroup());
                        $entity->setCareLevel($admission->getCareLevel());
                        $entity->setNotes($admission->getNotes());

                        $this->em->persist($entity);

                        $admission->setEnd($now);
                        $this->em->persist($admission);

                        break;
                    case GroupType::TYPE_APARTMENT:
                        /** @var ApartmentBedRepository $apartmentBedRepo */
                        $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

                        /** @var ApartmentBed $bed */
                        $bed = $apartmentBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $moveId);

                        if ($bed === null) {
                            throw new ApartmentBedNotFoundException();
                        }

                        $now = new \DateTime('now');

                        $entity = new ResidentAdmission();
                        $entity->setResident($admission->getResident());
                        $entity->setGroupType($admission->getGroupType());
                        $entity->setAdmissionType(AdmissionType::READMIT);
                        $entity->setStart($now);
                        $entity->setDate($now);
                        $entity->setApartmentBed($bed);
                        $entity->setNotes($admission->getNotes());

                        $this->em->persist($entity);

                        $admission->setEnd($now);
                        $this->em->persist($admission);

                        break;
                    case GroupType::TYPE_REGION:
                        throw new RegionCanNotHaveBedException();

                        break;
                    default:
                        throw new IncorrectStrategyTypeException();
                }
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param ResidentAdmission $entity
     * @param array $params
     * @param boolean $addMode
     * @return ResidentAdmission|null|object
     */
    private function saveAsFacility(ResidentAdmission $entity, array $params, bool $addMode)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var DiningRoomRepository $diningRoomRepo */
        $diningRoomRepo = $this->em->getRepository(DiningRoom::class);

        /** @var DiningRoom $diningRoom */
        $diningRoom = $diningRoomRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(DiningRoom::class), $params['dining_room_id']);

        /** @var FacilityBedRepository $facilityBedRepo */
        $facilityBedRepo = $this->em->getRepository(FacilityBed::class);

        /** @var FacilityBed $facilityBed */
        $facilityBed = $facilityBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $params['facility_bed_id']);

        /** @var CareLevelRepository $careLevelRepo */
        $careLevelRepo = $this->em->getRepository(CareLevel::class);

        /** @var CareLevel $careLevel */
        $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $params['care_level_id']);

        if ($diningRoom === null) {
            throw new DiningRoomNotFoundException();
        }

        if ($facilityBed === null) {
            throw new FacilityBedNotFoundException();
        }

        if ($careLevel === null) {
            throw new CareLevelNotFoundException();
        }

        $careGroup = $params['care_group'] ? (int)$params['care_group'] : 0;

        $entity->setDiningRoom($diningRoom);
        $entity->setFacilityBed($facilityBed);
        $entity->setDnr($params['dnr'] ?? false);
        $entity->setPolst($params['polst'] ?? false);
        $entity->setAmbulatory($params['ambulatory'] ?? false);
        $entity->setCareGroup($careGroup);
        $entity->setCareLevel($careLevel);

        $now = new \DateTime('now');

        $entity->setStart($now);

        if ($addMode) {
            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $lastAction */
            $lastAction = $admissionRepo->getLastAction($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $params['resident_id']);

            if ($lastAction !== null) {
                $lastAction->setEnd($now);

                $this->em->persist($lastAction);
            }
        }

        return $entity;
    }

    /**
     * @param ResidentAdmission $entity
     * @param array $params
     * @param boolean $addMode
     * @return ResidentAdmission|null|object
     */
    private function saveAsApartment(ResidentAdmission $entity, array $params, bool $addMode)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ApartmentBedRepository $apartmentBedRepo */
        $apartmentBedRepo = $this->em->getRepository(ApartmentBed::class);

        /** @var ApartmentBed $apartmentBed */
        $apartmentBed = $apartmentBedRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $params['apartment_bed_id']);

        if ($apartmentBed === null) {
            throw new ApartmentBedNotFoundException();
        }

        $entity->setApartmentBed($apartmentBed);

        $now = new \DateTime('now');

        $entity->setStart($now);

        if ($addMode) {
            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $lastAction */
            $lastAction = $admissionRepo->getLastAction($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $params['resident_id']);

            if ($lastAction !== null) {
                $lastAction->setEnd($now);

                $this->em->persist($lastAction);
            }
        }

        return $entity;
    }

    /**
     * @param ResidentAdmission $entity
     * @param array $params
     * @param boolean $addMode
     * @return ResidentAdmission|null|object
     */
    private function saveAsRegion(ResidentAdmission $entity, array $params, bool $addMode)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var RegionRepository $regionRepo */
        $regionRepo = $this->em->getRepository(Region::class);

        /** @var Region $region */
        $region = $regionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class), $params['region_id']);

        /** @var CityStateZipRepository $cszRepo */
        $cszRepo = $this->em->getRepository(CityStateZip::class);

        /** @var CityStateZip $csz */
        $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['csz_id']);

        /** @var CareLevelRepository $careLevelRepo */
        $careLevelRepo = $this->em->getRepository(CareLevel::class);

        /** @var CareLevel $careLevel */
        $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $params['care_level_id']);

        if ($region === null) {
            throw new RegionNotFoundException();
        }

        if ($csz === null) {
            throw new CityStateZipNotFoundException();
        }

        if ($careLevel === null) {
            throw new CareLevelNotFoundException();
        }

        $careGroup = $params['care_group'] ? (int)$params['care_group'] : 0;

        $entity->setRegion($region);
        $entity->setCsz($csz);
        $entity->setAddress($params['address']);
        $entity->setDnr($params['dnr'] ?? false);
        $entity->setPolst($params['polst'] ?? false);
        $entity->setAmbulatory($params['ambulatory'] ?? false);
        $entity->setCareGroup($careGroup);
        $entity->setCareLevel($careLevel);

        $now = new \DateTime('now');

        $entity->setStart($now);

        if ($addMode) {
            /** @var ResidentAdmissionRepository $admissionRepo */
            $admissionRepo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $lastAction */
            $lastAction = $admissionRepo->getLastAction($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $params['resident_id']);

            if ($lastAction !== null) {
                $lastAction->setEnd($now);

                $this->em->persist($lastAction);
            }
        }

        return $entity;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            /** @var ResidentAdmission $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $id);

            if ($entity === null) {
                throw new ResidentAdmissionNotFoundException();
            }

            $this->em->remove($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentAdmissionNotFoundException();
            }

            /** @var ResidentAdmissionRepository $repo */
            $repo = $this->em->getRepository(ResidentAdmission::class);

            $residentAdmissions = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $ids);

            if (empty($residentAdmissions)) {
                throw new ResidentAdmissionNotFoundException();
            }

            /**
             * @var ResidentAdmission $residentAdmission
             */
            foreach ($residentAdmissions as $residentAdmission) {
                $this->em->remove($residentAdmission);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
