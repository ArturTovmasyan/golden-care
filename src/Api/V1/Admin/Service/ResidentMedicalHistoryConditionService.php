<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicalHistoryConditionNotSingleException;
use App\Api\V1\Common\Service\Exception\MedicalHistoryConditionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentMedicalHistoryConditionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\MedicalHistoryCondition;
use App\Entity\Resident;
use App\Entity\ResidentMedicalHistoryCondition;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicalHistoryConditionService
 * @package App\Api\V1\Admin\Service
 */
class ResidentMedicalHistoryConditionService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rmhc.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentMedicalHistoryCondition::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentMedicalHistoryCondition::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentMedicalHistoryCondition|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentMedicalHistoryCondition::class)->getOne($this->grantService->getCurrentSpace(), $id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $params['resident_id']);

            /** @var MedicalHistoryCondition $medicalHistoryCondition */
            $medicalHistoryCondition = $this->em->getRepository(MedicalHistoryCondition::class)->getOne($currentSpace, $params['condition_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($medicalHistoryCondition === null) {
                throw new MedicalHistoryConditionNotFoundException();
            }

            $residentMedicalHistoryCondition = new ResidentMedicalHistoryCondition();
            $residentMedicalHistoryCondition->setResident($resident);
            $residentMedicalHistoryCondition->setCondition($medicalHistoryCondition);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
                $date->setTime(0, 0, 0);
            }

            $residentMedicalHistoryCondition->setDate($date);
            $residentMedicalHistoryCondition->setNotes($params['notes']);

            $this->validate($residentMedicalHistoryCondition, null, ['api_admin_resident_medical_history_condition_add']);

            $this->em->persist($medicalHistoryCondition);
            $this->em->persist($residentMedicalHistoryCondition);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentMedicalHistoryCondition $entity */
            $entity = $this->em->getRepository(ResidentMedicalHistoryCondition::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $params['resident_id']);

            /** @var MedicalHistoryCondition $medicalHistoryCondition */
            $medicalHistoryCondition = $this->em->getRepository(MedicalHistoryCondition::class)->getOne($currentSpace, $params['condition_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($medicalHistoryCondition === null) {
                throw new MedicalHistoryConditionNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setCondition($medicalHistoryCondition);

            $date = new \DateTime($params['date']);
            $date->setTime(0, 0, 0);

            $entity->setDate($date);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_medical_history_condition_edit']);

            $this->em->persist($medicalHistoryCondition);
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

            /** @var ResidentMedicalHistoryCondition $entity */
            $entity = $this->em->getRepository(ResidentMedicalHistoryCondition::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
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
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            $residentMedicalHistoryConditions = $this->em->getRepository(ResidentMedicalHistoryCondition::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($residentMedicalHistoryConditions)) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            /**
             * @var ResidentMedicalHistoryCondition $residentMedicalHistoryCondition
             */
            foreach ($residentMedicalHistoryConditions as $residentMedicalHistoryCondition) {
                $this->em->remove($residentMedicalHistoryCondition);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
