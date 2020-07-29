<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\QualificationRequirementNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\QualificationRequirement;
use App\Entity\Space;
use App\Repository\Lead\QualificationRequirementRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class QualificationRequirementService
 * @package App\Api\V1\Admin\Service
 */
class QualificationRequirementService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var QualificationRequirementRepository $repo */
        $repo = $this->em->getRepository(QualificationRequirement::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var QualificationRequirementRepository $repo */
        $repo = $this->em->getRepository(QualificationRequirement::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class));
    }

    /**
     * @param $id
     * @return QualificationRequirement|null|object
     */
    public function getById($id)
    {
        /** @var QualificationRequirementRepository $repo */
        $repo = $this->em->getRepository(QualificationRequirement::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class), $id);
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

            $qualificationRequirement = new QualificationRequirement();
            $qualificationRequirement->setTitle($params['title']);
            $qualificationRequirement->setUse($params['use']);
            $qualificationRequirement->setSpace($space);

            $this->validate($qualificationRequirement, null, ['api_lead_qualification_requirement_add']);

            $this->em->persist($qualificationRequirement);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $qualificationRequirement->getId();
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

            /** @var QualificationRequirementRepository $repo */
            $repo = $this->em->getRepository(QualificationRequirement::class);

            /** @var QualificationRequirement $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class), $id);

            if ($entity === null) {
                throw new QualificationRequirementNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setUse($params['use']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_lead_qualification_requirement_edit']);

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

            /** @var QualificationRequirementRepository $repo */
            $repo = $this->em->getRepository(QualificationRequirement::class);

            /** @var QualificationRequirement $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class), $id);

            if ($entity === null) {
                throw new QualificationRequirementNotFoundException();
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
                throw new QualificationRequirementNotFoundException();
            }

            /** @var QualificationRequirementRepository $repo */
            $repo = $this->em->getRepository(QualificationRequirement::class);

            $qualificationRequirements = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class), $ids);

            if (empty($qualificationRequirements)) {
                throw new QualificationRequirementNotFoundException();
            }

            /**
             * @var QualificationRequirement $qualificationRequirement
             */
            foreach ($qualificationRequirements as $qualificationRequirement) {
                $this->em->remove($qualificationRequirement);
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
            throw new QualificationRequirementNotFoundException();
        }

        /** @var QualificationRequirementRepository $repo */
        $repo = $this->em->getRepository(QualificationRequirement::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class), $ids);

        if (empty($entities)) {
            throw new QualificationRequirementNotFoundException();
        }

        return $this->getRelatedData(QualificationRequirement::class, $entities);
    }
}
