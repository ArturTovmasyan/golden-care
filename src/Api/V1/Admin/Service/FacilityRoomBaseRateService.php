<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\BaseRateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\BaseRateNotFoundException;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\DuplicateBaseRateByDateException;
use App\Api\V1\Common\Service\Exception\FacilityRoomTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\Row;
use App\Entity\Facility;
use App\Entity\FacilityRoomBaseRate;
use App\Entity\CareLevel;
use App\Entity\FacilityRoomBaseRateCareLevel;
use App\Entity\FacilityRoomType;
use App\Repository\FacilityRoomBaseRateCareLevelRepository;
use App\Repository\FacilityRoomBaseRateRepository;
use App\Repository\CareLevelRepository;
use App\Repository\FacilityRoomTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class FacilityRoomBaseRateService
 * @package App\Api\V1\Admin\Service
 */
class FacilityRoomBaseRateService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['room_type_id'])) {
            $roomTypeId = $params[0]['room_type_id'];

            $queryBuilder
                ->where('br.roomType = :roomTypeId')
                ->setParameter('roomTypeId', $roomTypeId);
        }
        
        /** @var FacilityRoomBaseRateRepository $repo */
        $repo = $this->em->getRepository(FacilityRoomBaseRate::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['room_type_id'])) {
            $roomTypeId = $params[0]['room_type_id'];

            /** @var FacilityRoomBaseRateRepository $repo */
            $repo = $this->em->getRepository(FacilityRoomBaseRate::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $roomTypeId);
        }

        throw new FacilityRoomTypeNotFoundException();
    }

    /**
     * @param $id
     * @return FacilityRoomBaseRate|null|object
     */
    public function getById($id)
    {
        /** @var FacilityRoomBaseRateRepository $repo */
        $repo = $this->em->getRepository(FacilityRoomBaseRate::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $id);
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

            $roomTypeId = $params['room_type_id'] ?? 0;

            /** @var FacilityRoomTypeRepository $roomTypeRepo */
            $roomTypeRepo = $this->em->getRepository(FacilityRoomType::class);

            /** @var FacilityRoomType $roomType */
            $roomType = $roomTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $roomTypeId);

            if ($roomType === null) {
                throw new FacilityRoomTypeNotFoundException();
            }

            $baseRate = new FacilityRoomBaseRate();
            $baseRate->setRoomType($roomType);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                /** @var FacilityRoomBaseRateRepository $repo */
                $repo = $this->em->getRepository(FacilityRoomBaseRate::class);

                /** @var FacilityRoomBaseRate $existingFacilityRoomBaseRate */
                $existingBaseRates = $repo->getByDate($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $roomTypeId, $date);

                if (!empty($existingBaseRates)) {
                    throw new DuplicateBaseRateByDateException();
                }
            }

            $baseRate->setDate($date);

            $levels = $this->saveLevels($currentSpace, $baseRate, $params['base_rates'] ?? []);

            if (\count($levels) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $baseRate->setLevels($levels);

            $this->validate($baseRate, null, ['api_admin_facility_room_base_rate_add']);

            $this->em->persist($baseRate);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $baseRate->getId();
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

            /** @var FacilityRoomBaseRateRepository $repo */
            $repo = $this->em->getRepository(FacilityRoomBaseRate::class);

            /** @var FacilityRoomBaseRate $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $id);

            if ($entity === null) {
                throw new BaseRateNotFoundException();
            }

            $roomTypeId = $params['room_type_id'] ?? 0;

            /** @var FacilityRoomTypeRepository $roomTypeRepo */
            $roomTypeRepo = $this->em->getRepository(FacilityRoomType::class);

            /** @var FacilityRoomType $roomType */
            $roomType = $roomTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(FacilityRoomType::class), $this->grantService->getCurrentUserEntityGrants(Facility::class), $roomTypeId);

            if ($roomType === null) {
                throw new FacilityRoomTypeNotFoundException();
            }

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $levels = $this->saveLevels($currentSpace, $entity, $params['base_rates'] ?? []);

            if (\count($levels) < 1) {
                throw new BaseRateNotBeBlankException();
            }

            $entity->setLevels($levels);

            $this->validate($entity, null, ['api_admin_facility_room_base_rate_edit']);

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
     * @param FacilityRoomBaseRate $baseRate
     * @param array $baseRates
     * @return array|null
     */
    private function saveLevels($currentSpace, FacilityRoomBaseRate $baseRate, array $baseRates = []): ?array
    {
        $validationGroup = 'api_admin_facility_room_base_rate_care_level_add';
        if ($baseRate->getId() !== null) {
            $validationGroup = 'api_admin_facility_room_base_rate_care_level_edit';

            /** @var FacilityRoomBaseRateCareLevelRepository $levelRepo */
            $levelRepo = $this->em->getRepository(FacilityRoomBaseRateCareLevel::class);

            $oldLevels = $levelRepo->getBy($baseRate->getId());

            foreach ($oldLevels as $oldLevel) {
                $this->em->remove($oldLevel);
            }
        }

        $baseRateLevels = [];

        foreach ($baseRates as $rate) {
            $careLevelId = $rate['care_level_id'] ?? 0;

            /** @var CareLevelRepository $careLevelRepo */
            $careLevelRepo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $careLevel */
            $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $careLevelId);

            if ($careLevel === null) {
                throw new CareLevelNotFoundException();
            }

            $amount = !empty($rate['amount']) ? $rate['amount'] : null;

            $level = new FacilityRoomBaseRateCareLevel();
            $level->setBaseRate($baseRate);
            $level->setCareLevel($careLevel);
            $level->setAmount($amount);

            $this->validate($level, null, [$validationGroup]);

            $this->em->persist($level);

            $baseRateLevels[] = $level;
        }

        return $baseRateLevels;
    }

    /**
     * @param $id
     * @throws \Throwable
     */
    public function remove($id): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var FacilityRoomBaseRateRepository $repo */
            $repo = $this->em->getRepository(FacilityRoomBaseRate::class);

            /** @var FacilityRoomBaseRate $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $id);

            if ($entity === null) {
                throw new BaseRateNotFoundException();
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
                throw new BaseRateNotFoundException();
            }

            /** @var FacilityRoomBaseRateRepository $repo */
            $repo = $this->em->getRepository(FacilityRoomBaseRate::class);

            $baseRates = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $ids);

            if (empty($baseRates)) {
                throw new BaseRateNotFoundException();
            }

            /**
             * @var FacilityRoomBaseRate $baseRate
             */
            foreach ($baseRates as $baseRate) {
                $this->em->remove($baseRate);
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
            throw new BaseRateNotFoundException();
        }

        /** @var FacilityRoomBaseRateRepository $repo */
        $repo = $this->em->getRepository(Row::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityRoomBaseRate::class), $ids);

        if (empty($entities)) {
            throw new BaseRateNotFoundException();
        }

        return $this->getRelatedData(FacilityRoomBaseRate::class, $entities);
    }
}
