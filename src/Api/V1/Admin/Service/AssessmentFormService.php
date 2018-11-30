<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCareLevelGroupNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentFormNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Allergen;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Form;
use App\Entity\Assessment\FormCategory;
use App\Entity\Assessment\Row;
use App\Entity\Space;
use App\Repository\Assessment\CategoryRepository;
use App\Repository\Assessment\FormCategoryRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentFormService
 * @package App\Api\V1\Admin\Service
 */
class AssessmentFormService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(Form::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(Form::class)->findAll();
    }

    /**
     * @param $id
     * @return Allergen|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(Form::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
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

            $form = new Form();
            $form->setTitle($params['title']);
            $form->setSpace($space);

            $this->validate($form, null, ['api_admin_assessment_form_add']);

            // add care level groups
            $groupIds = array_unique($params['care_level_groups']);
            $groups   = $this->em->getRepository(CareLevelGroup::class)->findByIds($groupIds);

            if (!empty($groups)) {
                $form->setCareLevelGroups($groups);
            }

            $this->em->persist($form);
            $this->em->flush();

            $this->saveCategories($form, $params['categories']);

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
             * @var CareLevelGroup $group
             * @var Form $form
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

            $form = $this->em->getRepository(Form::class)->find($id);

            if (is_null($form)) {
                throw new AssessmentFormNotFoundException();
            }

            $form->setTitle($params['title']);
            $form->setSpace($space);

            $this->validate($form, null, ['api_admin_assessment_form_edit']);

            // remove all care level groups
            $groups = $form->getCareLevelGroups();
            foreach ($groups as $group) {
                $form->removeCareLevelGroup($group);
            }

            // add care level groups
            $groupIds = array_unique($params['care_level_groups']);
            $groups   = $this->em->getRepository(CareLevelGroup::class)->findByIds($groupIds);

            if (!empty($groups)) {
                $form->setCareLevelGroups($groups);
            }

            $this->em->persist($form);
            $this->em->flush();

            $this->saveCategories($form, $params['categories']);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Form $form
     * @param array $categoryIds
     * @return bool
     */
    private function saveCategories(Form $form, $categoryIds = [])
    {
        /**
         * @var FormCategory $existingCategory
         * @var CategoryRepository $categoryRepository
         * @var Category $category
         */
        $existingCategories = $form->getFormCategories();
        $categoryIds        = array_unique($categoryIds);

        if (empty($categoryIds) && !empty($existingCategories)) {
            foreach ($existingCategories as $existingCategory) {
                $this->em->remove($existingCategory);
                $this->em->flush();
            }

            return true;
        }

        $existingCategoriesById = [];
        if (!empty($existingCategories)) {
            foreach ($existingCategories as $existingCategory) {
                $categoryId = $existingCategory->getCategory()->getId();
                $existingCategoriesById[$categoryId] = $existingCategory;

                if (!in_array($categoryId, $categoryIds)) {
                    $this->em->remove($existingCategory);
                    $this->em->flush();
                }
            }
        }

        $categoryRepository = $this->em->getRepository(Category::class);
        $categories         = $categoryRepository->findByIds($categoryIds);
        $categoriesById     = [];

        foreach ($categories as $category) {
            $categoriesById[$category->getId()] = $category;
        }

        foreach ($categoryIds as $key => $categoryId) {
            if (!isset($categoriesById[$categoryId])) {
                throw new AssessmentCategoryNotFoundException();
            }

            if (isset($existingCategoriesById[$categoryId])) {
                $existingCategoriesById[$categoryId]->setOrderNumber($key + 1);
                $this->em->persist($existingCategoriesById[$categoryId]);
            } else {
                $formCategory = new FormCategory();
                $formCategory->setForm($form);
                $formCategory->setCategory($categoriesById[$categoryId]);
                $formCategory->setOrderNumber($key + 1);
                $this->em->persist($formCategory);
            }
        }

        $this->em->flush();
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
            $form = $this->em->getRepository(Form::class)->find($id);

            if (is_null($form)) {
                throw new AssessmentFormNotFoundException();
            }

            // remove care level groups
            $groups = $form->getCareLevelGroups();
            foreach ($groups as $group) {
                $form->removeCareLevelGroup($group);
            }

            $this->em->remove($form);
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
            /**
             * @var Form $form
             */
            if (empty($ids)) {
                throw new AssessmentFormNotFoundException();
            }

            $forms = $this->em->getRepository(Form::class)->findByIds($ids);

            if (empty($forms)) {
                throw new AssessmentFormNotFoundException();
            }
            $this->em->getConnection()->beginTransaction();

            foreach ($forms as $form) {
                // remove care level groups
                $groups = $form->getCareLevelGroups();
                foreach ($groups as $group) {
                    $form->removeCareLevelGroup($group);
                }

                $this->em->remove($form);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch(AssessmentFormNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
