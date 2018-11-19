<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicalHistoryConditionNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\MedicalHistoryCondition;
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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $this->em->getRepository(MedicalHistoryCondition::class)->search($queryBuilder);
    }

    public function list($params)
    {
        return $this->em->getRepository(MedicalHistoryCondition::class)->findAll();
    }

    /**
     * @param $id
     * @return MedicalHistoryCondition|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(MedicalHistoryCondition::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $medicalHistoryCondition = new MedicalHistoryCondition();
            $medicalHistoryCondition->setTitle($params['title']);
            $medicalHistoryCondition->setDescription($params['description']);

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

            /** @var MedicalHistoryCondition $entity */
            $entity = $this->em->getRepository(MedicalHistoryCondition::class)->find($id);

            if ($entity === null) {
                throw new MedicalHistoryConditionNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setDescription($params['description']);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var MedicalHistoryCondition $entity */
            $entity = $this->em->getRepository(MedicalHistoryCondition::class)->find($id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            if (empty($ids)) {
                throw new MedicalHistoryConditionNotFoundException();
            }

            $conditions = $this->em->getRepository(MedicalHistoryCondition::class)->findByIds($ids);

            if (empty($conditions)) {
                throw new MedicalHistoryConditionNotFoundException();
            }

            /**
             * @var MedicalHistoryCondition $condition
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($conditions as $condition) {
                $this->em->remove($condition);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (MedicalHistoryConditionNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
