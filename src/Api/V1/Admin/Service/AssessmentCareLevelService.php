<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelGroupNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Assessment\CareLevel;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Category;
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
        $this->em->getRepository(CareLevel::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(CareLevel::class)->findAll();
    }

    /**
     * @param $id
     * @return Allergen|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(CareLevel::class)->find($id);
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
            $space            = null;
            $careLevelGroup   = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            if ($careLevelGroupId && $careLevelGroupId > 0) {
                $careLevelGroup = $this->em->getRepository(CareLevelGroup::class)->find($careLevelGroupId);

                if (is_null($careLevelGroup)) {
                    throw new AssessmentCareLevelGroupNotFoundException();
                }
            }

            $careLevel = new CareLevel();
            $careLevel->setTitle($params['title']);
            $careLevel->setSpace($space);
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

            $spaceId          = $params['space_id'] ?? 0;
            $careLevelGroupId = $params['care_level_group_id'] ?? 0;
            $space            = null;
            $careLevelGroup   = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if (is_null($space)) {
                    throw new SpaceNotFoundException();
                }
            }

            if ($careLevelGroupId && $careLevelGroupId > 0) {
                $careLevelGroup = $this->em->getRepository(CareLevelGroup::class)->find($careLevelGroupId);

                if (is_null($careLevelGroup)) {
                    throw new AssessmentCareLevelGroupNotFoundException();
                }
            }

            $careLevel = $this->em->getRepository(CareLevel::class)->find($id);

            if (is_null($careLevel)) {
                throw new AssessmentCareLevelNotFoundException();
            }

            $careLevel->setTitle($params['title']);
            $careLevel->setSpace($space);
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
            $careLevel = $this->em->getRepository(CareLevel::class)->find($id);

            if (is_null($careLevel)) {
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
            if (empty($ids)) {
                throw new AssessmentCareLevelNotFoundException();
            }

            $careLevels = $this->em->getRepository(CareLevel::class)->findByIds($ids);

            if (empty($careLevels)) {
                throw new AssessmentCareLevelNotFoundException();
            }

            /**
             * @var CareLevel $careLevel
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($careLevels as $careLevel) {
                $this->em->remove($careLevel);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch(AssessmentCareLevelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
