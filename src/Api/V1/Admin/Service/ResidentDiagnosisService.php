<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\DiagnosisNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentDiagnosisNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Diagnosis;
use App\Entity\Resident;
use App\Entity\ResidentDiagnosis;
use App\Repository\DiagnosisRepository;
use App\Repository\ResidentDiagnosisRepository;
use App\Repository\ResidentRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rd.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentDiagnosisRepository $repo */
        $repo = $this->em->getRepository(ResidentDiagnosis::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentDiagnosisRepository $repo */
            $repo = $this->em->getRepository(ResidentDiagnosis::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentDiagnosis|null|object
     */
    public function getById($id)
    {
        /** @var ResidentDiagnosisRepository $repo */
        $repo = $this->em->getRepository(ResidentDiagnosis::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $id);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var DiagnosisRepository $diagnosisRepo */
            $diagnosisRepo = $this->em->getRepository(Diagnosis::class);

            /** @var Diagnosis $diagnosis */
            $diagnosis = $diagnosisRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Diagnosis::class), $params['diagnosis_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($diagnosis === null) {
                throw new DiagnosisNotFoundException();
            }

            $residentDiagnosis = new ResidentDiagnosis();
            $residentDiagnosis->setResident($resident);
            $residentDiagnosis->setDiagnosis($diagnosis);
            $residentDiagnosis->setType($params['type']);
            $residentDiagnosis->setNotes($params['notes']);

            $this->validate($residentDiagnosis, null, ['api_admin_resident_diagnosis_add']);

            $this->em->persist($residentDiagnosis);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentDiagnosis->getId();
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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentDiagnosisRepository $repo */
            $repo = $this->em->getRepository(ResidentDiagnosis::class);

            /** @var ResidentDiagnosis $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $id);

            if ($entity === null) {
                throw new ResidentDiagnosisNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var DiagnosisRepository $diagnosisRepo */
            $diagnosisRepo = $this->em->getRepository(Diagnosis::class);

            /** @var Diagnosis $diagnosis */
            $diagnosis = $diagnosisRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Diagnosis::class), $params['diagnosis_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($diagnosis === null) {
                throw new DiagnosisNotFoundException();
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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentDiagnosisRepository $repo */
            $repo = $this->em->getRepository(ResidentDiagnosis::class);

            /** @var ResidentDiagnosis $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentDiagnosisNotFoundException();
            }

            /** @var ResidentDiagnosisRepository $repo */
            $repo = $this->em->getRepository(ResidentDiagnosis::class);

            $residentDiagnoses = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $ids);

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

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new ResidentDiagnosisNotFoundException();
        }

        /** @var ResidentDiagnosisRepository $repo */
        $repo = $this->em->getRepository(ResidentDiagnosis::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentDiagnosis::class), $ids);

        if (empty( $entities)) {
            throw new ResidentDiagnosisNotFoundException();
        }

        return $this->getRelatedData(ResidentDiagnosis::class, $entities);
    }
}
