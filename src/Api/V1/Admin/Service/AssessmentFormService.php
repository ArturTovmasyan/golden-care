<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\AssessmentFormNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\CareLevelGroup;
use App\Entity\Assessment\Category;
use App\Entity\Assessment\Form;
use App\Entity\Assessment\FormCategory;
use App\Entity\Space;
use App\Repository\Assessment\CareLevelGroupRepository;
use App\Repository\Assessment\CategoryRepository;
use App\Repository\Assessment\FormRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var FormRepository $repo */
        $repo = $this->em->getRepository(Form::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Form::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var FormRepository $repo */
        $repo = $this->em->getRepository(Form::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Form::class));
    }

    /**
     * @param $id
     * @return Form|null|object
     */
    public function getById($id)
    {
        /** @var FormRepository $repo */
        $repo = $this->em->getRepository(Form::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Form::class), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            /**
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $form = new Form();
            $form->setTitle($params['title']);
            $form->setSpace($space);

            $this->validate($form, null, ['api_admin_assessment_form_add']);

            /** @var CareLevelGroupRepository $careLevelGroupRepo */
            $careLevelGroupRepo = $this->em->getRepository(Form::class);

            // add care level groups
            $groupIds = array_unique($params['care_level_groups']);
            $groups = $careLevelGroupRepo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $groupIds);

            if (!empty($groups)) {
                $form->setCareLevelGroups($groups);
            }

            $this->em->persist($form);
            $this->em->flush();

            $this->saveCategories($form, $params['categories']);

            $this->em->getConnection()->commit();

            $insert_id = $form->getId();
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
             * @var CareLevelGroup $group
             * @var Form $form
             * @var Space $space
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            /** @var FormRepository $repo */
            $repo = $this->em->getRepository(Form::class);

            $form = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Form::class), $id);

            if ($form === null) {
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

            /** @var CareLevelGroupRepository $careLevelGroupRepo */
            $careLevelGroupRepo = $this->em->getRepository(Form::class);

            // add care level groups
            $groupIds = array_unique($params['care_level_groups']);
            $groups = $careLevelGroupRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevelGroup::class), $groupIds);

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

        if (empty($categoryIds) && $existingCategories !== null) {
            foreach ($existingCategories as $existingCategory) {
                $this->em->remove($existingCategory);
            }
            $this->em->flush();
            return true;
        }

        $existingCategoriesById = [];
        if ($existingCategories !== null) {
            foreach ($existingCategories as $existingCategory) {
                $categoryId = $existingCategory->getCategory()->getId();
                $existingCategoriesById[$categoryId] = $existingCategory;

                if (!\in_array($categoryId, $categoryIds, false)) {
                    $this->em->remove($existingCategory);
                    $this->em->flush();
                }
            }
        }

        $categoryRepository = $this->em->getRepository(Category::class);
        $categories         = $categoryRepository->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Category::class), $categoryIds);
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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var FormRepository $repo */
            $repo = $this->em->getRepository(Form::class);

            /** @var Form $form */
            $form = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Form::class), $id);

            if ($form === null) {
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
     * @throws \Throwable
     */
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new AssessmentFormNotFoundException();
            }

            /** @var FormRepository $repo */
            $repo = $this->em->getRepository(Form::class);

            $forms = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Form::class), $ids);

            if (empty($forms)) {
                throw new AssessmentFormNotFoundException();
            }

            /**
             * @var Form $form
             */
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
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
