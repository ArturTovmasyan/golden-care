<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DocumentCategoryNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\DocumentCategory;
use App\Entity\Space;
use App\Repository\DocumentCategoryRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DocumentCategoryService
 * @package App\Api\V1\Admin\Service
 */
class DocumentCategoryService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var DocumentCategoryRepository $repo */
        $repo = $this->em->getRepository(DocumentCategory::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var DocumentCategoryRepository $repo */
        $repo = $this->em->getRepository(DocumentCategory::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class));
    }

    /**
     * @param $id
     * @return DocumentCategory|null|object
     */
    public function getById($id)
    {
        /** @var DocumentCategoryRepository $repo */
        $repo = $this->em->getRepository(DocumentCategory::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $id);
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
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $documentCategory = new DocumentCategory();
            $documentCategory->setTitle($params['title']);
            $documentCategory->setSpace($space);

            $this->validate($documentCategory, null, ['api_admin_document_category_add']);

            $this->em->persist($documentCategory);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $documentCategory->getId();
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

            $this->em->getConnection()->beginTransaction();

            /** @var DocumentCategoryRepository $repo */
            $repo = $this->em->getRepository(DocumentCategory::class);

            /** @var DocumentCategory $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $id);

            if ($entity === null) {
                throw new DocumentCategoryNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_document_category_edit']);

            $this->em->persist($entity);
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

            /** @var DocumentCategoryRepository $repo */
            $repo = $this->em->getRepository(DocumentCategory::class);

            /** @var DocumentCategory $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $id);

            if ($entity === null) {
                throw new DocumentCategoryNotFoundException();
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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new DocumentCategoryNotFoundException();
            }

            /** @var DocumentCategoryRepository $repo */
            $repo = $this->em->getRepository(DocumentCategory::class);

            $documentCategories = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $ids);

            if (empty($documentCategories)) {
                throw new DocumentCategoryNotFoundException();
            }

            /**
             * @var DocumentCategory $documentCategory
             */
            foreach ($documentCategories as $documentCategory) {
                $this->em->remove($documentCategory);
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
            throw new DocumentCategoryNotFoundException();
        }

        /** @var DocumentCategoryRepository $repo */
        $repo = $this->em->getRepository(DocumentCategory::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(DocumentCategory::class), $ids);

        if (empty($entities)) {
            throw new DocumentCategoryNotFoundException();
        }

        return $this->getRelatedData(DocumentCategory::class, $entities);
    }
}
