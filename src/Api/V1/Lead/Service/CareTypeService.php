<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\CareTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\CareType;
use App\Entity\Space;
use App\Repository\Lead\CareTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class CareTypeService
 * @package App\Api\V1\Admin\Service
 */
class CareTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var CareTypeRepository $repo */
        $repo = $this->em->getRepository(CareType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var CareTypeRepository $repo */
        $repo = $this->em->getRepository(CareType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareType::class));
    }

    /**
     * @param $id
     * @return CareType|null|object
     */
    public function getById($id)
    {
        /** @var CareTypeRepository $repo */
        $repo = $this->em->getRepository(CareType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareType::class), $id);
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

            $careType = new CareType();
            $careType->setTitle($params['title']);
            $careType->setSpace($space);

            $this->validate($careType, null, ['api_lead_care_type_add']);

            $this->em->persist($careType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $careType->getId();
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

            /** @var CareTypeRepository $repo */
            $repo = $this->em->getRepository(CareType::class);

            /** @var CareType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareType::class), $id);

            if ($entity === null) {
                throw new CareTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_care_type_edit']);

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

            /** @var CareTypeRepository $repo */
            $repo = $this->em->getRepository(CareType::class);

            /** @var CareType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareType::class), $id);

            if ($entity === null) {
                throw new CareTypeNotFoundException();
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
                throw new CareTypeNotFoundException();
            }

            /** @var CareTypeRepository $repo */
            $repo = $this->em->getRepository(CareType::class);

            $careTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareType::class), $ids);

            if (empty($careTypes)) {
                throw new CareTypeNotFoundException();
            }

            /**
             * @var CareType $careType
             */
            foreach ($careTypes as $careType) {
                $this->em->remove($careType);
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
            throw new CareTypeNotFoundException();
        }

        /** @var CareTypeRepository $repo */
        $repo = $this->em->getRepository(CareType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CareType::class), $ids);

        if (empty($entities)) {
            throw new CareTypeNotFoundException();
        }

        return $this->getRelatedData(CareType::class, $entities);
    }
}
