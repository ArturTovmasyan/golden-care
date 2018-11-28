<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Row;
use App\Entity\Space;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Category::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Category::class)->findAll();
    }

    /**
     * @param $id
     * @return Allergen|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Category::class)->find($id);
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

            $category = new Category();
            $category->setTitle($params['title']);
            $category->setSpace($space);
            $category->setMultiItem($params['multi_item']);

            $this->validate($category, null, ['api_admin_assessment_category_add']);
            $this->em->persist($category);

            if (!empty($params['rows'])) {
                foreach ($params['rows'] as $row) {
                    $entity = new Row();
                    $entity->setTitle($row['title'] ?? '');
                    $entity->setScore($row['score'] ?? 0);
                    $entity->setCategory($category);

                    $this->validate($entity, null, ['api_admin_assessment_row_add']);
                    $this->em->persist($entity);
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

            $spaceId = $params['space_id'] ?? 0;
            $space   = null;

            if ($spaceId && $spaceId > 0) {
                $space = $this->em->getRepository(Space::class)->find($spaceId);

                if ($space === null) {
                    throw new SpaceNotFoundException();
                }
            }

            $category = $this->em->getRepository(Category::class)->find($id);

            if (is_null($category)) {
                throw new AssessmentCategoryNotFoundException();
            }

            $category->setTitle($params['title']);
            $category->setSpace($space);
            $category->setMultiItem($params['multi_item']);

            $this->validate($category, null, ['api_admin_assessment_category_edit']);
            $this->em->persist($category);

            // remove old rows
            $rows = $category->getRows();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $category->removeRow($row);
                    $this->em->remove($row);
                }
            }

            // create new rows
            if (!empty($params['rows'])) {
                foreach ($params['rows'] as $row) {
                    $entity = new Row();
                    $entity->setTitle($row['title'] ?? '');
                    $entity->setScore($row['score'] ?? 0);
                    $entity->setCategory($category);

                    $this->validate($entity, null, ['api_admin_assessment_row_add']);
                    $this->em->persist($entity);
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
     * @param $id
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Category $category */
            $Category = $this->em->getRepository(Category::class)->find($id);

            if (is_null($Category)) {
                throw new AssessmentCategoryNotFoundException();
            }

            $this->em->remove($Category);
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
                throw new AssessmentCategoryNotFoundException();
            }

            $categories = $this->em->getRepository(Allergen::class)->findByIds($ids);

            if (empty($categories)) {
                throw new AssessmentCategoryNotFoundException();
            }

            /**
             * @var Category $category
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($categories as $category) {
                $rows = $category->getRows();

                foreach ($rows as $row) {
                    $this->em->remove($row);
                }

                $this->em->remove($category);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch(AssessmentCategoryNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
