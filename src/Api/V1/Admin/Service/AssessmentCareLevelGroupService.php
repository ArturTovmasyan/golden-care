<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelGroupNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Row;
use App\Entity\Space;
use App\Repository\Assessment\CareLevelGroupRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var CareLevelGroupRepository $repo */
        $repo = $this->em->getRepository(CareLevelGroup::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CareLevelGroupRepository $repo */
        $repo = $this->em->getRepository(CareLevelGroup::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class));
    }

    /**
     * @param $id
     * @return CareLevelGroup|null|object
     */
    public function getById($id)
    {
        /** @var CareLevelGroupRepository $repo */
        $repo = $this->em->getRepository(CareLevelGroup::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            /**
             * @var Category $entity
             * @var Row $row
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $careLevelGroup = new CareLevelGroup();
            $careLevelGroup->setTitle($params['title']);
            $careLevelGroup->setSpace($space);

            $this->validate($careLevelGroup, null, ['api_admin_assessment_care_level_group_add']);
            $this->em->persist($careLevelGroup);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $careLevelGroup->getId();
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
    public function edit($id, array $params) : void
    {
        try {
            /**
             * @var CareLevelGroup $careLevelGroup
             * @var Row $row
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CareLevelGroupRepository $repo */
            $repo = $this->em->getRepository(CareLevelGroup::class);

            $careLevelGroup = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $id);

            if ($careLevelGroup === null) {
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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var CareLevelGroupRepository $repo */
            $repo = $this->em->getRepository(CareLevelGroup::class);

            /** @var CareLevelGroup $careLevelGroup */
            $careLevelGroup = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $id);

            if ($careLevelGroup === null) {
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
     * @throws \Throwable
     */
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            /** @var CareLevelGroupRepository $repo */
            $repo = $this->em->getRepository(CareLevelGroup::class);

            $careLevelGroups = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $ids);

            if (empty($careLevelGroups)) {
                throw new AssessmentCareLevelGroupNotFoundException();
            }

            /**
             * @var CareLevelGroup $careLevelGroup
             */
            foreach ($careLevelGroups as $careLevelGroup) {
                $this->em->remove($careLevelGroup);
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
            throw new AssessmentCareLevelGroupNotFoundException();
        }

        /** @var CareLevelGroupRepository $repo */
        $repo = $this->em->getRepository(CareLevelGroup::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $ids);

        if (empty($entities)) {
            throw new AssessmentCareLevelGroupNotFoundException();
        }

        return $this->getRelatedData(CareLevelGroup::class, $entities);
    }
}
