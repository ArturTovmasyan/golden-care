<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\BaseRateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityRoomTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\BaseRate;
use App\Entity\CareLevel;
use App\Entity\FacilityRoomType;
use App\Entity\Facility;
use App\Repository\BaseRateRepository;
use App\Repository\CareLevelRepository;
use App\Repository\FacilityRoomTypeRepository;
use App\Repository\FacilityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRoomTypeService
 * @package App\Api\V1\Admin\Service
 */
class FacilityRoomTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        $facilityId = null;
        if (!empty($params) || !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];
        }

        /** @var FacilityRoomTypeRepository $repo */
        $repo = $this->em->getRepository(FacilityRoomType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $queryBuilder, $facilityId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();
        $entityGrants = $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class);
        $facilityEntityGrants = $this->grantService->getCurrentUserEntityGrants(Facility::class);

        /** @var FacilityRoomTypeRepository $repo */
        $repo = $this->em->getRepository(FacilityRoomType::class);

        if (!empty($params) && !empty($params[0]['facility_id'])) {
            $facilityId = $params[0]['facility_id'];

            return $repo->getBy($currentSpace, $entityGrants, $facilityEntityGrants, $facilityId);
        }

        return $repo->list($currentSpace, $entityGrants, $facilityEntityGrants);
    }

    /**
     * @param $id
     * @return FacilityRoomType|null|object
     */
    public function getById($id)
    {
        /** @var FacilityRoomTypeRepository $repo */
        $repo = $this->em->getRepository(FacilityRoomType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $facilityRoomType = new FacilityRoomType();
            $facilityRoomType->setFacility($facility);
            $facilityRoomType->setTitle($params['title']);
            $facilityRoomType->setPrivate($params['private']);
            $facilityRoomType->setDescription($params['description'] ?? '');

            $baseRates = $this->saveBaseRates($currentSpace, $facilityRoomType, $params['base_rates'] ?? []);

            if (\count($baseRates) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $facilityRoomType->setBaseRates($baseRates);

            $this->validate($facilityRoomType, null, ['api_admin_facility_room_type_add']);

            $this->em->persist($facilityRoomType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $facilityRoomType->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var FacilityRoomTypeRepository $repo */
            $repo = $this->em->getRepository(FacilityRoomType::class);

            /** @var FacilityRoomType $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityRoomTypeNotFoundException();
            }

            $facilityId = $params['facility_id'] ?? 0;

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            /** @var Facility $facility */
            $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

            if ($facility === null) {
                throw new FacilityNotFoundException();
            }

            $entity->setFacility($facility);
            $entity->setTitle($params['title']);
            $entity->setPrivate($params['private']);
            $entity->setDescription($params['description'] ?? '');

            $baseRates = $this->saveBaseRates($currentSpace, $entity, $params['base_rates'] ?? []);

            if (\count($baseRates) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $entity->setBaseRates($baseRates);

            $this->validate($entity, null, ['api_admin_facility_room_type_edit']);

            $this->em->persist($entity);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $currentSpace
     * @param FacilityRoomType $facilityRoomType
     * @param array $baseRates
     * @return array|null
     */
    private function saveBaseRates($currentSpace, FacilityRoomType $facilityRoomType, array $baseRates = []): ?array
    {
        $validationGroup = 'api_admin_base_rate_add';
        if ($facilityRoomType->getId() !== null) {
            $validationGroup = 'api_admin_base_rate_edit';

            /** @var BaseRateRepository $baseRateRepo */
            $baseRateRepo = $this->em->getRepository(BaseRate::class);

            $oldRates = $baseRateRepo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(BaseRate::class), $facilityRoomType);

            foreach ($oldRates as $rate) {
                $this->em->remove($rate);
            }
        }

        $facilityRoomTypeBaseRates = [];

        foreach ($baseRates as $baseRate) {
            $careLevelId = $baseRate['care_level_id'] ?? 0;

            /** @var CareLevelRepository $careLevelRepo */
            $careLevelRepo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $careLevel */
            $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $careLevelId);

            if ($careLevel === null) {
                throw new CareLevelNotFoundException();
            }

            $amount = !empty($baseRate['amount']) ? $baseRate['amount'] : null;

            $facilityRoomTypeBaseRate = new BaseRate();
            $facilityRoomTypeBaseRate->setRoomType($facilityRoomType);
            $facilityRoomTypeBaseRate->setCareLevel($careLevel);
            $facilityRoomTypeBaseRate->setAmount($amount);

            $this->validate($facilityRoomTypeBaseRate, null, [$validationGroup]);

            $this->em->persist($facilityRoomTypeBaseRate);

            $facilityRoomTypeBaseRates[] = $facilityRoomTypeBaseRate;
        }

        return $facilityRoomTypeBaseRates;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var FacilityRoomTypeRepository $repo */
            $repo = $this->em->getRepository(FacilityRoomType::class);

            /** @var FacilityRoomType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $id);

            if ($entity === null) {
                throw new FacilityRoomTypeNotFoundException();
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
                throw new FacilityRoomTypeNotFoundException();
            }

            /** @var FacilityRoomTypeRepository $repo */
            $repo = $this->em->getRepository(FacilityRoomType::class);

            $facilityRoomTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

            if (empty($facilityRoomTypes)) {
                throw new FacilityRoomTypeNotFoundException();
            }

            /**
             * @var FacilityRoomType $facilityRoomType
             */
            foreach ($facilityRoomTypes as $facilityRoomType) {
                $this->em->remove($facilityRoomType);
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
            throw new FacilityRoomTypeNotFoundException();
        }

        /** @var FacilityRoomTypeRepository $repo */
        $repo = $this->em->getRepository(FacilityRoomType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $ids);

        if (empty($entities)) {
            throw new FacilityRoomTypeNotFoundException();
        }

        return $this->getRelatedData(FacilityRoomType::class, $entities);
    }
}
