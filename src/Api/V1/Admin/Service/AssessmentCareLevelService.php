<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelGroupNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\CareLevel;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Row;
use App\Repository\Assessment\CareLevelGroupRepository;
use App\Repository\Assessment\CareLevelRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentCategoryService
 * @package App\Api\V1\Admin\Service
 */
class AssessmentCareLevelService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class));
    }

    /**
     * @param $id
     * @return CareLevel|null|object
     */
    public function getById($id)
    {
        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $id);
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
            /**
             * @var CareLevelGroup $careLevelGroup
             */
            $this->em->getConnection()->beginTransaction();

            $careLevelGroupId = $params['care_level_group_id'] ?? 0;

            /** @var CareLevelGroupRepository $careLevelGroupRepo */
            $careLevelGroupRepo = $this->em->getRepository(CareLevelGroup::class);

            $careLevelGroup = $careLevelGroupRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $careLevelGroupId);

            if ($careLevelGroup === null) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            $careLevel = new CareLevel();
            $careLevel->setTitle($params['title']);
            $careLevel->setLevelLow($params['level_low']);
            $careLevel->setLevelHigh($params['level_high']);
            $careLevel->setCareLevelGroup($careLevelGroup);

            $this->validate($careLevel, null, ['api_admin_assessment_care_level_add']);
            $this->em->persist($careLevel);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $careLevel->getId();
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
            /**
             * @var CareLevel $careLevel
             * @var CareLevelGroup $careLevelGroup
             * @var Row $row
             */
            $this->em->getConnection()->beginTransaction();

            $careLevelGroupId = $params['care_level_group_id'] ?? 0;

            /** @var CareLevelGroupRepository $careLevelGroupRepo */
            $careLevelGroupRepo = $this->em->getRepository(CareLevelGroup::class);

            $careLevelGroup = $careLevelGroupRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $careLevelGroupId);

            if ($careLevelGroup === null) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            /** @var CareLevelRepository $repo */
            $repo = $this->em->getRepository(CareLevel::class);

            $careLevel = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $id);

            if ($careLevel === null) {
                throw new AssessmentCareLevelNotFoundException();
            }

            $careLevel->setTitle($params['title']);
            $careLevel->setLevelLow($params['level_low']);
            $careLevel->setLevelHigh($params['level_high']);
            $careLevel->setCareLevelGroup($careLevelGroup);

            $this->validate($careLevel, null, ['api_admin_assessment_care_level_edit']);
            $this->em->persist($careLevel);

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

            /** @var CareLevelRepository $repo */
            $repo = $this->em->getRepository(CareLevel::class);

            /** @var CareLevel $careLevel */
            $careLevel = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $id);

            if ($careLevel === null) {
                throw new AssessmentCareLevelNotFoundException();
            }

            $this->em->remove($careLevel);
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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new AssessmentCareLevelNotFoundException();
            }

            /** @var CareLevelRepository $repo */
            $repo = $this->em->getRepository(CareLevel::class);

            $careLevels = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $ids);

            if (empty($careLevels)) {
                throw new AssessmentCareLevelNotFoundException();
            }

            /**
             * @var CareLevel $careLevel
             */
            foreach ($careLevels as $careLevel) {
                $this->em->remove($careLevel);
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
            throw new AssessmentCareLevelNotFoundException();
        }

        /** @var CareLevelRepository $repo */
        $repo = $this->em->getRepository(CareLevel::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $ids);

        if (empty($entities)) {
            throw new AssessmentCareLevelNotFoundException();
        }

        return $this->getRelatedData(CareLevel::class, $entities);
    }
}
