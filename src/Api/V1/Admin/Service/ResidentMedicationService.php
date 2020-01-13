<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationFormFactorNotFoundException;
use App\Api\V1\Common\Service\Exception\MedicationNotFoundException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentMedicationNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use App\Entity\MedicationFormFactor;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentMedication;
use App\Repository\MedicationFormFactorRepository;
use App\Repository\MedicationRepository;
use App\Repository\PhysicianRepository;
use App\Repository\ResidentMedicationRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicationService
 * @package App\Api\V1\Admin\Service
 */
class ResidentMedicationService extends BaseService implements IGridService
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
            ->where('rm.resident = :residentId')
            ->setParameter('residentId', $residentId);

        if ((int)$params[0]['discontinued'] !== 1) {
            $queryBuilder
                ->andWhere('rm.discontinued = :discontinued')
                ->setParameter('discontinued', 0);
        }

        /** @var ResidentMedicationRepository $repo */
        $repo = $this->em->getRepository(ResidentMedication::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentMedicationRepository $repo */
            $repo = $this->em->getRepository(ResidentMedication::class);

            $medication_id = null;

            if (!empty($params[0]['medication_id'])) {
                $medication_id = $params[0]['medication_id'];
            }

            $noDiscontinued = false;

            if ((int)$params[0]['discontinued'] !== 1) {
                $noDiscontinued = true;
            }

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $residentId, $medication_id, $noDiscontinued);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentMedication|null|object
     */
    public function getById($id)
    {
        /** @var ResidentMedicationRepository $repo */
        $repo = $this->em->getRepository(ResidentMedication::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $medicationId = $params['medication_id'] ?? 0;
            $formFactorId = $params['form_factor_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var PhysicianRepository $physicianRepo */
            $physicianRepo = $this->em->getRepository(Physician::class);

            /** @var Physician $physician */
            $physician = $physicianRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Physician::class), $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            /** @var MedicationRepository $medicationRepo */
            $medicationRepo = $this->em->getRepository(Medication::class);

            /** @var Medication $medication */
            $medication = $medicationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Medication::class), $medicationId);

            if ($medication === null) {
                throw new MedicationNotFoundException();
            }

            /** @var MedicationFormFactorRepository $medicationFormFactorRepo */
            $medicationFormFactorRepo = $this->em->getRepository(MedicationFormFactor::class);

            /** @var MedicationFormFactor $formFactor */
            $formFactor = $medicationFormFactorRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class), $formFactorId);

            if ($formFactor === null) {
                throw new MedicationFormFactorNotFoundException();
            }

            $residentMedication = new ResidentMedication();
            $residentMedication->setResident($resident);
            $residentMedication->setPhysician($physician);
            $residentMedication->setMedication($medication);
            $residentMedication->setFormFactor($formFactor);
            $residentMedication->setDosage($params['dosage']);
            $residentMedication->setDosageUnit($params['dosage_unit']);
            $residentMedication->setAm($params['am']);
            $residentMedication->setNn($params['nn']);
            $residentMedication->setPm($params['pm']);
            $residentMedication->setHs($params['hs']);
            $residentMedication->setPrn($params['prn']);
            $residentMedication->setDiscontinued($params['discontinued']);
            $residentMedication->setTreatment($params['treatment']);
            $residentMedication->setNotes($params['notes']);
            $residentMedication->setPrescriptionNumber($params['prescription_number']);

            $this->validate($residentMedication, null, ['api_admin_resident_medication_add']);

            $this->em->persist($residentMedication);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentMedication->getId();
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
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentMedicationRepository $repo */
            $repo = $this->em->getRepository(ResidentMedication::class);

            /** @var ResidentMedication $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $id);

            if ($entity === null) {
                throw new ResidentMedicationNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $medicationId = $params['medication_id'] ?? 0;
            $formFactorId = $params['form_factor_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var PhysicianRepository $physicianRepo */
            $physicianRepo = $this->em->getRepository(Physician::class);

            /** @var Physician $physician */
            $physician = $physicianRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Physician::class), $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            /** @var MedicationRepository $medicationRepo */
            $medicationRepo = $this->em->getRepository(Medication::class);

            /** @var Medication $medication */
            $medication = $medicationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Medication::class), $medicationId);

            if ($medication === null) {
                throw new MedicationNotFoundException();
            }

            /** @var MedicationFormFactorRepository $medicationFormFactorRepo */
            $medicationFormFactorRepo = $this->em->getRepository(MedicationFormFactor::class);

            /** @var MedicationFormFactor $formFactor */
            $formFactor = $medicationFormFactorRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(MedicationFormFactor::class), $formFactorId);

            if ($formFactor === null) {
                throw new MedicationFormFactorNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setPhysician($physician);
            $entity->setMedication($medication);
            $entity->setFormFactor($formFactor);
            $entity->setDosage($params['dosage']);
            $entity->setDosageUnit($params['dosage_unit']);
            $entity->setAm($params['am']);
            $entity->setNn($params['nn']);
            $entity->setPm($params['pm']);
            $entity->setHs($params['hs']);
            $entity->setPrn($params['prn']);
            $entity->setDiscontinued($params['discontinued']);
            $entity->setTreatment($params['treatment']);
            $entity->setNotes($params['notes']);
            $entity->setPrescriptionNumber($params['prescription_number']);

            $this->validate($entity, null, ['api_admin_resident_medication_edit']);

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

            /** @var ResidentMedicationRepository $repo */
            $repo = $this->em->getRepository(ResidentMedication::class);

            /** @var ResidentMedication $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $id);

            if ($entity === null) {
                throw new ResidentMedicationNotFoundException();
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
                throw new ResidentMedicationNotFoundException();
            }

            /** @var ResidentMedicationRepository $repo */
            $repo = $this->em->getRepository(ResidentMedication::class);

            $residentMedications = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $ids);

            if (empty($residentMedications)) {
                throw new ResidentMedicationNotFoundException();
            }

            /**
             * @var ResidentMedication $residentMedication
             */
            foreach ($residentMedications as $residentMedication) {
                $this->em->remove($residentMedication);
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
            throw new ResidentMedicationNotFoundException();
        }

        /** @var ResidentMedicationRepository $repo */
        $repo = $this->em->getRepository(ResidentMedication::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedication::class), $ids);

        if (empty($entities)) {
            throw new ResidentMedicationNotFoundException();
        }

        return $this->getRelatedData(ResidentMedication::class, $entities);
    }
}
