<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Row;
use App\Entity\Space;
use App\Repository\Assessment\CategoryRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentCategoryService
 * @package App\Api\V1\Admin\Service
 */
class AssessmentCategoryService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var CategoryRepository $repo */
        $repo = $this->em->getRepository(Category::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Category::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CategoryRepository $repo */
        $repo = $this->em->getRepository(Category::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Category::class));
    }

    /**
     * @param $id
     * @return Category|null|object
     */
    public function getById($id)
    {
        /** @var CategoryRepository $repo */
        $repo = $this->em->getRepository(Category::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Category::class), $id);
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

            $category = new Category();
            $category->setTitle($params['title']);
            $category->setSpace($space);
            $category->setMultiItem($params['multi_item'] ? true : false);

            $this->validate($category, null, ['api_admin_assessment_category_add']);
            $this->em->persist($category);

            $this->saveRows($category, $params['rows']);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $category->getId();
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
            /**
             * @var Category $category
             * @var Row $row
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var CategoryRepository $repo */
            $repo = $this->em->getRepository(Category::class);

            $category = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Category::class), $id);

            if ($category === null) {
                throw new AssessmentCategoryNotFoundException();
            }

            $category->setTitle($params['title']);
            $category->setSpace($space);
            $category->setMultiItem($params['multi_item'] ? true : false);

            $this->validate($category, null, ['api_admin_assessment_category_edit']);
            $this->em->persist($category);

            $this->saveRows($category, $params['rows']);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Category $category
     * @param $rows
     * @throws \ReflectionException
     */
    private function saveRows(Category $category, $rows)
    {
        /**
         * @var Row $oldRow
         */
        $oldRows      = $category->getRows();
        $oldRowsByIds = [];
        $rowIds       = [];

        if ($oldRows !== null) {
            foreach ($oldRows as $oldRow) {
                $oldRowsByIds[$oldRow->getId()] = $oldRow;
            }
        }

        // create and update row
        if (!empty($rows)) {
            foreach ($rows as $key => $row) {
                if (isset($row['id'], $oldRowsByIds[$row['id']])) {
                    $entity   = $oldRowsByIds[$row['id']];
                    $rowIds[] = $row['id'];
                } else {
                    $entity = new Row();
                }

                $entity->setTitle($row['title'] ?? '');
                $entity->setScore($row['score'] ?? 0);
                $entity->setCategory($category);
                $entity->setOrderNumber($key + 1);

                $this->validate($entity, null, ['api_admin_assessment_row_add']);
                $this->em->persist($entity);
            }
        }

        // remove old rows
        if ($oldRows !== null) {
            foreach ($oldRows as $oldRow) {
                if (!\in_array($oldRow->getId(), $rowIds, false)) {
                    $this->em->remove($oldRow);
                    $this->em->flush();
                }
            }
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

            /** @var CategoryRepository $repo */
            $repo = $this->em->getRepository(Category::class);

            /** @var Category $category */
            $category = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Category::class), $id);

            if ($category === null) {
                throw new AssessmentCategoryNotFoundException();
            }

            $this->em->remove($category);
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
                throw new AssessmentCategoryNotFoundException();
            }

            /** @var CategoryRepository $repo */
            $repo = $this->em->getRepository(Category::class);

            $categories = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Category::class), $ids);

            if (empty($categories)) {
                throw new AssessmentCategoryNotFoundException();
            }

            /**
             * @var Category $category
             */
            foreach ($categories as $category) {
                $rows = $category->getRows();

                foreach ($rows as $row) {
                    $this->em->remove($row);
                }

                $this->em->remove($category);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
