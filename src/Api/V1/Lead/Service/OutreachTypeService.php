<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\OutreachTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\OutreachType;
use App\Entity\Space;
use App\Repository\Lead\OutreachTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class OutreachTypeService
 * @package App\Api\V1\Admin\Service
 */
class OutreachTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var OutreachTypeRepository $repo */
        $repo = $this->em->getRepository(OutreachType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var OutreachTypeRepository $repo */
        $repo = $this->em->getRepository(OutreachType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OutreachType::class));
    }

    /**
     * @param $id
     * @return OutreachType|null|object
     */
    public function getById($id)
    {
        /** @var OutreachTypeRepository $repo */
        $repo = $this->em->getRepository(OutreachType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $id);
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

            $outreachType = new OutreachType();
            $outreachType->setTitle($params['title']);
            $outreachType->setSpace($space);

            $this->validate($outreachType, null, ['api_lead_outreach_type_add']);

            $this->em->persist($outreachType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $outreachType->getId();
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

            /** @var OutreachTypeRepository $repo */
            $repo = $this->em->getRepository(OutreachType::class);

            /** @var OutreachType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $id);

            if ($entity === null) {
                throw new OutreachTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_outreach_type_edit']);

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

            /** @var OutreachTypeRepository $repo */
            $repo = $this->em->getRepository(OutreachType::class);

            /** @var OutreachType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $id);

            if ($entity === null) {
                throw new OutreachTypeNotFoundException();
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
                throw new OutreachTypeNotFoundException();
            }

            /** @var OutreachTypeRepository $repo */
            $repo = $this->em->getRepository(OutreachType::class);

            $outreachTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $ids);

            if (empty($outreachTypes)) {
                throw new OutreachTypeNotFoundException();
            }

            /**
             * @var OutreachType $outreachType
             */
            foreach ($outreachTypes as $outreachType) {
                $this->em->remove($outreachType);
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
            throw new OutreachTypeNotFoundException();
        }

        /** @var OutreachTypeRepository $repo */
        $repo = $this->em->getRepository(OutreachType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(OutreachType::class), $ids);

        if (empty($entities)) {
            throw new OutreachTypeNotFoundException();
        }

        return $this->getRelatedData(OutreachType::class, $entities);
    }
}
