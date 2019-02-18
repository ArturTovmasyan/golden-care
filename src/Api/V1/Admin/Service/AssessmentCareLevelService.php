<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelGroupNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\CareLevel;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Row;
use App\Entity\Space;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(CareLevel::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(CareLevel::class)->list($this->grantService->getCurrentSpace());
    }

    /**
     * @param $id
     * @return CareLevel|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(CareLevel::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var CareLevelGroup $careLevelGroup
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $spaceId          = $params['space_id'] ?? 0;
            $careLevelGroupId = $params['care_level_group_id'] ?? 0;

            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $careLevelGroup = $this->em->getRepository(CareLevelGroup::class)->getOne($this->grantService->getCurrentSpace(), $careLevelGroupId);

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
    public function edit($id, array $params) : void
    {
        try {
            /**
             * @var CareLevel $careLevel
             * @var CareLevelGroup $careLevelGroup
             * @var Row $row
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $spaceId          = $params['space_id'] ?? 0;
            $careLevelGroupId = $params['care_level_group_id'] ?? 0;

            $space = $this->em->getRepository(Space::class)->find($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $careLevelGroup = $this->em->getRepository(CareLevelGroup::class)->getOne($currentSpace, $careLevelGroupId);

            if ($careLevelGroup === null) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            $careLevel = $this->em->getRepository(CareLevel::class)->getOne($currentSpace, $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var CareLevel $careLevel */
            $careLevel = $this->em->getRepository(CareLevel::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new AssessmentCareLevelNotFoundException();
            }

            $careLevels = $this->em->getRepository(CareLevel::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

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
}
