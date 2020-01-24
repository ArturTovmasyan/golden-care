<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\FacilityBed;
use App\Entity\FacilityEvent;
use App\Entity\ResidentAdmission;
use App\Entity\ResidentEvent;
use App\Entity\ResidentRent;
use App\Entity\ResidentRentIncrease;
use App\Entity\Space;
use App\Model\GroupType;
use App\Repository\CityStateZipRepository;
use App\Repository\FacilityBedRepository;
use App\Repository\FacilityEventRepository;
use App\Repository\FacilityRepository;
use App\Repository\ResidentAdmissionRepository;
use App\Repository\ResidentEventRepository;
use App\Repository\ResidentRentIncreaseRepository;
use App\Repository\ResidentRentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityService
 * @package App\Api\V1\Admin\Service
 */
class FacilityService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        $entityGrants = $this->grantService->getCurrentUserEntityGrants(Facility::class);
        if (!empty($params) && isset($params[0]['all'])) {
            $entityGrants = null;
        }

        return $repo->list($this->grantService->getCurrentSpace(), $entityGrants);
    }

    /**
     * @param $id
     * @return Facility|null|object
     */
    public function getById($id)
    {
        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        /** @var Facility $facility */
        $facility = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

        if ($facility !== null) {
            /** @var FacilityBedRepository $bedRepo */
            $bedRepo = $this->em->getRepository(FacilityBed::class);

            $bedsConfigured = $bedRepo->getBedCount($facility->getId());

            $facility->setBedsConfigured($bedsConfigured);
        }

        return $facility;
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $cszId = $params['csz_id'] ?? 0;

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $facility = new Facility();
            $facility->setName($params['name']);
            $facility->setDescription($params['description']);
            $facility->setShorthand($params['shorthand']);
            $facility->setPhone($params['phone']);
            $facility->setFax($params['fax']);
            $facility->setAddress($params['address']);
            $facility->setLicense($params['license']);
            $facility->setCsz($csz);
            $facility->setBedsLicensed((int)$params['beds_licensed']);
            $facility->setBedsTarget((int)$params['beds_target']);
            $facility->setNumberOfFloors((int)$params['number_of_floors']);
            $facility->setRedFlag((int)$params['red_flag']);
            $facility->setYellowFlag((int)$params['yellow_flag']);
            $facility->setSpace($space);

            $this->validate($facility, null, ['api_admin_facility_add']);

            $this->em->persist($facility);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $facility->getId();
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
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRepository $repo */
            $repo = $this->em->getRepository(Facility::class);

            /** @var Facility $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityNotFoundException();
            }

            $cszId = $params['csz_id'] ?? 0;

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CityStateZipRepository $cszRepo */
            $cszRepo = $this->em->getRepository(CityStateZip::class);

            /** @var CityStateZip $csz */
            $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $cszId);

            if ($csz === null) {
                throw new CityStateZipNotFoundException();
            }

            $entity->setName($params['name']);
            $entity->setDescription($params['description']);
            $entity->setShorthand($params['shorthand']);
            $entity->setPhone($params['phone']);
            $entity->setFax($params['fax']);
            $entity->setAddress($params['address']);
            $entity->setLicense($params['license']);
            $entity->setCsz($csz);
            $entity->setBedsLicensed((int)$params['beds_licensed']);
            $entity->setBedsTarget((int)$params['beds_target']);
            $entity->setNumberOfFloors((int)$params['number_of_floors']);
            $entity->setRedFlag((int)$params['red_flag']);
            $entity->setYellowFlag((int)$params['yellow_flag']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_facility_edit']);

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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var FacilityRepository $repo */
            $repo = $this->em->getRepository(Facility::class);

            /** @var Facility $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityNotFoundException();
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
                throw new FacilityNotFoundException();
            }

            /** @var FacilityRepository $repo */
            $repo = $this->em->getRepository(Facility::class);

            $facilities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

            if (empty($facilities)) {
                throw new FacilityNotFoundException();
            }

            /**
             * @var Facility $facility
             */
            foreach ($facilities as $facility) {
                $this->em->remove($facility);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new FacilityNotFoundException();
        }

        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

        if (empty($entities)) {
            throw new FacilityNotFoundException();
        }

        return $this->getRelatedData(Facility::class, $entities);
    }

    /**
     * @param $id
     * @param $dateFrom
     * @param $dateTo
     * @param $definitionId
     * @return array
     */
    public function getCalendar($id, $dateFrom, $dateTo, $definitionId): array
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        if (!empty($dateFrom)) {
            $dateFrom = new \DateTime($dateFrom);
            $dateFrom = $dateFrom->format('Y-m-d 00:00:00');
        }

        if (!empty($dateTo)) {
            $dateTo = new \DateTime($dateTo);
            $dateTo = $dateTo->format('Y-m-d 23:59:59');
        }

        $definitionId = !empty($definitionId) ? (int)$definitionId : null;

        /** @var FacilityEventRepository $eventRepo */
        $eventRepo = $this->em->getRepository(FacilityEvent::class);
        $facilityEvents = $eventRepo->getFacilityCalendarData($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityEvent::class), $id, $dateFrom, $dateTo);

        /** @var ResidentAdmissionRepository $admissionRepo */
        $admissionRepo = $this->em->getRepository(ResidentAdmission::class);
        $residents = $admissionRepo->getActiveResidentsByStrategy($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), GroupType::TYPE_FACILITY, $id);

        $residentIds = [];
        if (!empty($residents)) {
            $residentIds = array_map(function (array $item) {
                return $item['id'];
            }, $residents);
        }

        $admissions = $admissionRepo->getResidentsCalendarData($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentAdmission::class), $residentIds, $dateFrom, $dateTo);

        /** @var ResidentRentRepository $rentRepo */
        $rentRepo = $this->em->getRepository(ResidentRent::class);
        $rents = $rentRepo->getResidentsCalendarData($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRent::class), $residentIds, $dateFrom, $dateTo);

        /** @var ResidentRentIncreaseRepository $rentIncreaseRepo */
        $rentIncreaseRepo = $this->em->getRepository(ResidentRentIncrease::class);
        $rentIncreases = $rentIncreaseRepo->getResidentsCalendarData($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentRentIncrease::class), $residentIds, $dateFrom, $dateTo);

        /** @var ResidentEventRepository $residentEventRepo */
        $residentEventRepo = $this->em->getRepository(ResidentEvent::class);
        $residentEvents = $residentEventRepo->getResidentsCalendarData($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $residentIds, $dateFrom, $dateTo, $definitionId);

        return [
            'facility_events' => $facilityEvents,
            'admissions' => $admissions,
            'rents' => $rents,
            'rent_increases' => $rentIncreases,
            'resident_events' => $residentEvents,
        ];
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getMobileList($date)
    {
        /** @var FacilityRepository $repo */
        $repo = $this->em->getRepository(Facility::class);

        $entities = $repo->mobileList($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $date);

        $finalEntities = [];
        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $entity['updated_at'] = $entity['updated_at'] !== null ? $entity['updated_at']->format('Y-m-d H:i:s') : $entity['updated_at'];

                $finalEntities[] = $entity;
            }
        }

        return $finalEntities;
    }
}
