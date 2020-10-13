<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\KeyFinanceTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\KeyFinanceType;
use App\Entity\Space;
use App\Repository\KeyFinanceTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class KeyFinanceTypeService
 * @package App\Api\V1\Admin\Service
 */
class KeyFinanceTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var KeyFinanceTypeRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var KeyFinanceTypeRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class));
    }

    /**
     * @param $id
     * @return KeyFinanceType|null|object
     */
    public function getById($id)
    {
        /** @var KeyFinanceTypeRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class), $id);
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
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $type = $params['type'] ? (int)$params['type'] : 0;

            $keyFinanceType = new KeyFinanceType();
            $keyFinanceType->setType($type);
            $keyFinanceType->setTitle($params['title']);
            $keyFinanceType->setDescription($params['description']);
            $keyFinanceType->setSpace($space);

            $this->validate($keyFinanceType, null, ['api_admin_key_finance_type_add']);

            $this->em->persist($keyFinanceType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $keyFinanceType->getId();
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

            $this->em->getConnection()->beginTransaction();

            /** @var KeyFinanceTypeRepository $repo */
            $repo = $this->em->getRepository(KeyFinanceType::class);

            /** @var KeyFinanceType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class), $id);

            if ($entity === null) {
                throw new KeyFinanceTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_key_finance_type_edit']);

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

            /** @var KeyFinanceTypeRepository $repo */
            $repo = $this->em->getRepository(KeyFinanceType::class);

            /** @var KeyFinanceType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class), $id);

            if ($entity === null) {
                throw new KeyFinanceTypeNotFoundException();
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
                throw new KeyFinanceTypeNotFoundException();
            }

            /** @var KeyFinanceTypeRepository $repo */
            $repo = $this->em->getRepository(KeyFinanceType::class);

            $keyFinanceTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class), $ids);

            if (empty($keyFinanceTypes)) {
                throw new KeyFinanceTypeNotFoundException();
            }

            /**
             * @var KeyFinanceType $keyFinanceType
             */
            foreach ($keyFinanceTypes as $keyFinanceType) {
                $this->em->remove($keyFinanceType);
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
            throw new KeyFinanceTypeNotFoundException();
        }

        /** @var KeyFinanceTypeRepository $repo */
        $repo = $this->em->getRepository(KeyFinanceType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(KeyFinanceType::class), $ids);

        if (empty($entities)) {
            throw new KeyFinanceTypeNotFoundException();
        }

        return $this->getRelatedData(KeyFinanceType::class, $entities);
    }
}
