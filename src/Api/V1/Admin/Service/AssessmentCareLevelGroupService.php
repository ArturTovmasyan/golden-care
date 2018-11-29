<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelGroupNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Row;
use App\Entity\Space;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentCategoryService
 * @package App\Api\V1\Admin\Service
 */
class AssessmentCareLevelGroupService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(CareLevelGroup::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(CareLevelGroup::class)->findAll();
    }

    /**
     * @param $id
     * @return Allergen|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(CareLevelGroup::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Category $entity
             * @var Row $row
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;
            $space   = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $careLevelGroup = new CareLevelGroup();
            $careLevelGroup->setTitle($params['title']);
            $careLevelGroup->setSpace($space);

            $this->validate($careLevelGroup, null, ['api_admin_assessment_care_level_group_add']);
            $this->em->persist($careLevelGroup);

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
             * @var CareLevelGroup $careLevelGroup
             * @var Row $row
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;
            $space   = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $careLevelGroup = $this->em->getRepository(CareLevelGroup::class)->find($id);

            if (is_null($careLevelGroup)) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            $careLevelGroup->setTitle($params['title']);
            $careLevelGroup->setSpace($space);

            $this->validate($careLevelGroup, null, ['api_admin_assessment_care_level_group_edit']);
            $this->em->persist($careLevelGroup);

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

            /** @var CareLevelGroup $careLevelGroup */
            $careLevelGroup = $this->em->getRepository(CareLevelGroup::class)->find($id);

            if (is_null($careLevelGroup)) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            $this->em->remove($careLevelGroup);
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
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            $careLevelGroups = $this->em->getRepository(CareLevelGroup::class)->findByIds($ids);

            if (empty($careLevelGroups)) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            /**
             * @var CareLevelGroup $careLevelGroup
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($careLevelGroups as $careLevelGroup) {
                $this->em->remove($careLevelGroup);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch(AssessmentCareLevelGroupNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
