<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Assessment\AssessmentType;
use App\Entity\Space;
use App\Repository\Assessment\AssessmentTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AssessmentTypeService
 * @package App\Api\V1\Admin\Service
 */
class AssessmentTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var AssessmentTypeRepository $repo */
        $repo = $this->em->getRepository(AssessmentType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var AssessmentTypeRepository $repo */
        $repo = $this->em->getRepository(AssessmentType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentType::class));
    }

    /**
     * @param $id
     * @return AssessmentType|null|object
     */
    public function getById($id)
    {
        /** @var AssessmentTypeRepository $repo */
        $repo = $this->em->getRepository(AssessmentType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentType::class), $id);
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
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $assessmentType = new AssessmentType();
            $assessmentType->setTitle($params['title']);
            $assessmentType->setSpace($space);

            $this->validate($assessmentType, null, ['api_admin_assessment_type_add']);

            $this->em->persist($assessmentType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $assessmentType->getId();
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

            $this->em->getConnection()->beginTransaction();

            /** @var AssessmentTypeRepository $repo */
            $repo = $this->em->getRepository(AssessmentType::class);

            /** @var AssessmentType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentType::class), $id);

            if ($entity === null) {
                throw new AssessmentTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_assessment_type_edit']);

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

            /** @var AssessmentTypeRepository $repo */
            $repo = $this->em->getRepository(AssessmentType::class);

            /** @var AssessmentType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentType::class), $id);

            if ($entity === null) {
                throw new AssessmentTypeNotFoundException();
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
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new AssessmentTypeNotFoundException();
            }

            /** @var AssessmentTypeRepository $repo */
            $repo = $this->em->getRepository(AssessmentType::class);

            $assessmentTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentType::class), $ids);

            if (empty($assessmentTypes)) {
                throw new AssessmentTypeNotFoundException();
            }

            /**
             * @var AssessmentType $assessmentType
             */
            foreach ($assessmentTypes as $assessmentType) {
                $this->em->remove($assessmentType);
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
            throw new AssessmentTypeNotFoundException();
        }

        /** @var AssessmentTypeRepository $repo */
        $repo = $this->em->getRepository(AssessmentType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(AssessmentType::class), $ids);

        if (empty($entities)) {
            throw new AssessmentTypeNotFoundException();
        }

        return $this->getRelatedData(AssessmentType::class, $entities);
    }
}
