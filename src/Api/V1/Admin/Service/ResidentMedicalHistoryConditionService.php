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

        $this->em->getRepository(ResidentMedicalHistoryCondition::class)->search($queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentMedicalHistoryCondition::class)->findBy(['resident' => $residentId]);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentMedicalHistoryCondition|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentMedicalHistoryCondition::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $residentId = $params['resident_id'] ?? 0;

            $resident = null;

            if ($residentId && $residentId > 0) {
                /** @var Resident $resident */
                $resident = $this->em->getRepository(Resident::class)->find($residentId);

                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            $medicalHistoryConditionId = $params['condition_id'];
            $newMedicalHistoryCondition = $params['condition'];

            if ((empty($medicalHistoryConditionId) && empty($newMedicalHistoryCondition)) || (!empty($medicalHistoryConditionId) && !empty($newMedicalHistoryCondition))) {
                throw new MedicalHistoryConditionNotSingleException();
            }

            $medicalHistoryCondition = null;

            if (!empty($newMedicalHistoryCondition)) {
                $newMedicalHistoryConditionTitle = $newMedicalHistoryCondition['title'] ?? '';
                $newMedicalHistoryConditionDescription = $newMedicalHistoryCondition['description'] ?? '';

                $medicalHistoryCondition = new MedicalHistoryCondition();
                $medicalHistoryCondition->setTitle($newMedicalHistoryConditionTitle);
                $medicalHistoryCondition->setDescription($newMedicalHistoryConditionDescription);
                $medicalHistoryCondition->setSpace($resident->getSpace());
            }

            if (!empty($medicalHistoryConditionId)) {
                /** @var MedicalHistoryCondition $medicalHistoryCondition */
                $medicalHistoryCondition = $this->em->getRepository(MedicalHistoryCondition::class)->find($medicalHistoryConditionId);

                if ($medicalHistoryCondition === null) {
                    throw new MedicalHistoryConditionNotFoundException();
                }
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

            /** @var ResidentMedicalHistoryCondition $entity */
            $entity = $this->em->getRepository(ResidentMedicalHistoryCondition::class)->find($id);

            if ($entity === null) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            $resident = null;

            if ($residentId && $residentId > 0) {
                /** @var Resident $resident */
                $resident = $this->em->getRepository(Resident::class)->find($residentId);

                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            $medicalHistoryConditionId = $params['condition_id'];
            $newMedicalHistoryCondition = $params['condition'];

            if ((empty($medicalHistoryConditionId) && empty($newMedicalHistoryCondition)) || (!empty($medicalHistoryConditionId) && !empty($newMedicalHistoryCondition))) {
                throw new MedicalHistoryConditionNotSingleException();
            }

            $medicalHistoryCondition = null;

            if (!empty($newMedicalHistoryCondition)) {
                $newMedicalHistoryConditionTitle = $newMedicalHistoryCondition['title'] ?? '';
                $newMedicalHistoryConditionDescription = $newMedicalHistoryCondition['description'] ?? '';

                $medicalHistoryCondition = new MedicalHistoryCondition();
                $medicalHistoryCondition->setTitle($newMedicalHistoryConditionTitle);
                $medicalHistoryCondition->setDescription($newMedicalHistoryConditionDescription);
                $medicalHistoryCondition->setSpace($resident->getSpace());
            }

            if (!empty($medicalHistoryConditionId)) {
                /** @var MedicalHistoryCondition $medicalHistoryCondition */
                $medicalHistoryCondition = $this->em->getRepository(MedicalHistoryCondition::class)->find($medicalHistoryConditionId);

                if ($medicalHistoryCondition === null) {
                    throw new MedicalHistoryConditionNotFoundException();
                }
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
            $entity = $this->em->getRepository(ResidentMedicalHistoryCondition::class)->find($id);

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
            if (empty($ids)) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            $residentMedicalHistoryConditions = $this->em->getRepository(ResidentMedicalHistoryCondition::class)->findByIds($ids);

            if (empty($residentMedicalHistoryConditions)) {
                throw new ResidentMedicalHistoryConditionNotFoundException();
            }

            /**
             * @var ResidentMedicalHistoryCondition $residentMedicalHistoryCondition
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentMedicalHistoryConditions as $residentMedicalHistoryCondition) {
                $this->em->remove($residentMedicalHistoryCondition);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentMedicalHistoryConditionNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
