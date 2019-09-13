<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\ReferrerTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\ReferrerType;
use App\Entity\Space;
use App\Repository\Lead\ReferrerTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ReferrerTypeService
 * @package App\Api\V1\Admin\Service
 */
class ReferrerTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ReferrerTypeRepository $repo */
        $repo = $this->em->getRepository(ReferrerType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ReferrerTypeRepository $repo */
        $repo = $this->em->getRepository(ReferrerType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class));
    }

    /**
     * @param $id
     * @return ReferrerType|null|object
     */
    public function getById($id)
    {
        /** @var ReferrerTypeRepository $repo */
        $repo = $this->em->getRepository(ReferrerType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $id);
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

            $referrerType = new ReferrerType();
            $referrerType->setTitle($params['title']);
            $referrerType->setOrganizationRequired($params['organization_required']);
            $referrerType->setRepresentativeRequired($params['representative_required']);
            $referrerType->setSpace($space);

            $this->validate($referrerType, null, ['api_lead_referrer_type_add']);

            $this->em->persist($referrerType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $referrerType->getId();
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

            /** @var ReferrerTypeRepository $repo */
            $repo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $id);

            if ($entity === null) {
                throw new ReferrerTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setOrganizationRequired($params['organization_required']);
            $entity->setRepresentativeRequired($params['representative_required']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_referrer_type_edit']);

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

            /** @var ReferrerTypeRepository $repo */
            $repo = $this->em->getRepository(ReferrerType::class);

            /** @var ReferrerType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $id);

            if ($entity === null) {
                throw new ReferrerTypeNotFoundException();
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
                throw new ReferrerTypeNotFoundException();
            }

            /** @var ReferrerTypeRepository $repo */
            $repo = $this->em->getRepository(ReferrerType::class);

            $referrerTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $ids);

            if (empty($referrerTypes)) {
                throw new ReferrerTypeNotFoundException();
            }

            /**
             * @var ReferrerType $referrerType
             */
            foreach ($referrerTypes as $referrerType) {
                $this->em->remove($referrerType);
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
            throw new ReferrerTypeNotFoundException();
        }

        /** @var ReferrerTypeRepository $repo */
        $repo = $this->em->getRepository(ReferrerType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $ids);

        if (empty($entities)) {
            throw new ReferrerTypeNotFoundException();
        }

        return $this->getRelatedData(ReferrerType::class, $entities);
    }
}
