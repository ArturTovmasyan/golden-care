<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicalHistoryConditionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentMedicalHistoryConditionNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\MedicalHistoryCondition;
use App\Entity\Resident;
use App\Entity\ResidentMedicalHistoryCondition;
use App\Repository\MedicalHistoryConditionRepository;
use App\Repository\ResidentMedicalHistoryConditionRepository;
use App\Repository\ResidentRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rmhc.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentMedicalHistoryConditionRepository $repo */
        $repo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentMedicalHistoryConditionRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentMedicalHistoryCondition|null|object
     */
    public function getById($id)
    {
        /** @var ResidentMedicalHistoryConditionRepository $repo */
        $repo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $id);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var MedicalHistoryConditionRepository $medicalHistoryConditionRepo */
            $medicalHistoryConditionRepo = $this->em->getRepository(MedicalHistoryCondition::class);

            /** @var MedicalHistoryCondition $medicalHistoryCondition */
            $medicalHistoryCondition = $medicalHistoryConditionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class), $params['condition_id']);

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

            $this->em->persist($residentMedicalHistoryCondition);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentMedicalHistoryCondition->getId();
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentMedicalHistoryConditionRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

            /** @var ResidentMedicalHistoryCondition $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $id);

            if ($entity === null) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var MedicalHistoryConditionRepository $medicalHistoryConditionRepo */
            $medicalHistoryConditionRepo = $this->em->getRepository(MedicalHistoryCondition::class);

            /** @var MedicalHistoryCondition $medicalHistoryCondition */
            $medicalHistoryCondition = $medicalHistoryConditionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(MedicalHistoryCondition::class), $params['condition_id']);

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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentMedicalHistoryConditionRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

            /** @var ResidentMedicalHistoryCondition $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            /** @var ResidentMedicalHistoryConditionRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

            $residentMedicalHistoryConditions = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $ids);

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

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new ResidentMedicalHistoryConditionNotFoundException();
        }

        /** @var ResidentMedicalHistoryConditionRepository $repo */
        $repo = $this->em->getRepository(ResidentMedicalHistoryCondition::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicalHistoryCondition::class), $ids);

        if (empty($entities)) {
            throw new ResidentMedicalHistoryConditionNotFoundException();
        }

        return $this->getRelatedData(ResidentMedicalHistoryCondition::class, $entities);
    }
}
