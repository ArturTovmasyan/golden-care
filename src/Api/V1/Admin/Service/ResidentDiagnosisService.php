<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiagnosisNotSingleException;
use App\Api\V1\Common\Service\Exception\DiagnosisNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentDiagnosisNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diagnosis;
use App\Entity\Resident;
use App\Entity\ResidentDiagnosis;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentDiagnosisService
 * @package App\Api\V1\Admin\Service
 */
class ResidentDiagnosisService extends BaseService implements IGridService
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
            ->where('rd.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentDiagnosis::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentDiagnosis::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentDiagnosis|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentDiagnosis::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $residentId = $params['resident_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $diagnosisId = $params['diagnosis_id'];
            $newDiagnosis = $params['diagnosis'];

            if ((empty($diagnosisId) && empty($newDiagnosis)) || (!empty($diagnosisId) && !empty($newDiagnosis))) {
                throw new DiagnosisNotSingleException();
            }

            $diagnosis = null;

            if (!empty($newDiagnosis)) {
                $newDiagnosisTitle = $newDiagnosis['title'] ?? '';
                $newDiagnosisAcronym = $newDiagnosis['acronym'] ?? '';
                $newDiagnosisDescription = $newDiagnosis['description'] ?? '';

                $diagnosis = new Diagnosis();
                $diagnosis->setTitle($newDiagnosisTitle);
                $diagnosis->setAcronym($newDiagnosisAcronym);
                $diagnosis->setDescription($newDiagnosisDescription);
                $diagnosis->setSpace($resident->getSpace());
            }

            if (!empty($diagnosisId)) {
                /** @var Diagnosis $diagnosis */
                $diagnosis = $this->em->getRepository(Diagnosis::class)->getOne($currentSpace, $diagnosisId);

                if ($diagnosis === null) {
                    throw new DiagnosisNotFoundException();
                }
            }

            $residentDiagnosis = new ResidentDiagnosis();
            $residentDiagnosis->setResident($resident);
            $residentDiagnosis->setDiagnosis($diagnosis);
            $residentDiagnosis->setType($params['type']);
            $residentDiagnosis->setNotes($params['notes']);

            $this->validate($residentDiagnosis, null, ['api_admin_resident_diagnosis_add']);

            $this->em->persist($diagnosis);
            $this->em->persist($residentDiagnosis);
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

            /** @var ResidentDiagnosis $entity */
            $entity = $this->em->getRepository(ResidentDiagnosis::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ResidentDiagnosisNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $diagnosisId = $params['diagnosis_id'];
            $newDiagnosis = $params['diagnosis'];

            if ((empty($diagnosisId) && empty($newDiagnosis)) || (!empty($diagnosisId) && !empty($newDiagnosis))) {
                throw new DiagnosisNotSingleException();
            }

            $diagnosis = null;

            if (!empty($newDiagnosis)) {
                $newDiagnosisTitle = $newDiagnosis['title'] ?? '';
                $newDiagnosisAcronym = $newDiagnosis['acronym'] ?? '';
                $newDiagnosisDescription = $newDiagnosis['description'] ?? '';

                $diagnosis = new Diagnosis();
                $diagnosis->setTitle($newDiagnosisTitle);
                $diagnosis->setAcronym($newDiagnosisAcronym);
                $diagnosis->setDescription($newDiagnosisDescription);
                $diagnosis->setSpace($resident->getSpace());
            }

            if (!empty($diagnosisId)) {
                /** @var Diagnosis $diagnosis */
                $diagnosis = $this->em->getRepository(Diagnosis::class)->getOne($currentSpace, $diagnosisId);

                if ($diagnosis === null) {
                    throw new DiagnosisNotFoundException();
                }
            }

            $entity->setResident($resident);
            $entity->setDiagnosis($diagnosis);
            $entity->setType($params['type']);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_diagnosis_edit']);

            $this->em->persist($diagnosis);
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

            /** @var ResidentDiagnosis $entity */
            $entity = $this->em->getRepository(ResidentDiagnosis::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
                throw new ResidentDiagnosisNotFoundException();
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
                throw new ResidentDiagnosisNotFoundException();
            }

             $residentDiagnoses = $this->em->getRepository(ResidentDiagnosis::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty( $residentDiagnoses)) {
                throw new ResidentDiagnosisNotFoundException();
            }

            /**
             * @var ResidentDiagnosis $residentDiagnosis
             */
            foreach ( $residentDiagnoses as $residentDiagnosis) {
                $this->em->remove($residentDiagnosis);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
