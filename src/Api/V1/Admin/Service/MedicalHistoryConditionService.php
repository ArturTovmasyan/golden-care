<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicalHistoryConditionNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\MedicalHistoryCondition;
use App\Entity\Space;
use App\Repository\MedicalHistoryConditionRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicalHistoryConditionService
 * @package App\Api\V1\Admin\Service
 */
class MedicalHistoryConditionService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var MedicalHistoryConditionRepository $repo */
        $repo = $this->em->getRepository(MedicalHistoryCondition::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var MedicalHistoryConditionRepository $repo */
        $repo = $this->em->getRepository(MedicalHistoryCondition::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class));
    }

    /**
     * @param $id
     * @return MedicalHistoryCondition|null|object
     */
    public function getById($id)
    {
        /** @var MedicalHistoryConditionRepository $repo */
        $repo = $this->em->getRepository(MedicalHistoryCondition::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $medicalHistoryCondition = new MedicalHistoryCondition();
            $medicalHistoryCondition->setTitle($params['title']);
            $medicalHistoryCondition->setDescription($params['description']);
            $medicalHistoryCondition->setSpace($space);

            $this->validate($medicalHistoryCondition, null, ['api_admin_medical_history_condition_add']);

            $this->em->persist($medicalHistoryCondition);
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

            $this->em->getConnection()->beginTransaction();

            /** @var MedicalHistoryConditionRepository $repo */
            $repo = $this->em->getRepository(MedicalHistoryCondition::class);

            /** @var MedicalHistoryCondition $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class), $id);

            if ($entity === null) {
                throw new MedicalHistoryConditionNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_medical_history_condition_edit']);

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

            /** @var MedicalHistoryConditionRepository $repo */
            $repo = $this->em->getRepository(MedicalHistoryCondition::class);

            /** @var MedicalHistoryCondition $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class), $id);

            if ($entity === null) {
                throw new MedicalHistoryConditionNotFoundException();
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
                throw new MedicalHistoryConditionNotFoundException();
            }

            /** @var MedicalHistoryConditionRepository $repo */
            $repo = $this->em->getRepository(MedicalHistoryCondition::class);

            $conditions = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class), $ids);

            if (empty($conditions)) {
                throw new MedicalHistoryConditionNotFoundException();
            }

            /**
             * @var MedicalHistoryCondition $condition
             */
            foreach ($conditions as $condition) {
                $this->em->remove($condition);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
