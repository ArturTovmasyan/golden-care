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
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rm.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentMedication::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentMedication::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentMedication|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentMedication::class)->getOne($this->grantService->getCurrentSpace(), $id);
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
            $physicianId = $params['physician_id'] ?? 0;
            $medicationId = $params['medication_id'] ?? 0;
            $formFactorId = $params['form_factor_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var Physician $physician */
            $physician = $this->em->getRepository(Physician::class)->getOne($currentSpace, $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            /** @var Medication $medication */
            $medication = $this->em->getRepository(Medication::class)->getOne($currentSpace, $medicationId);

            if ($medication === null) {
                throw new MedicationNotFoundException();
            }

            /** @var MedicationFormFactor $formFactor */
            $formFactor = $this->em->getRepository(MedicationFormFactor::class)->getOne($currentSpace, $formFactorId);

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

            /** @var ResidentMedication $entity */
            $entity = $this->em->getRepository(ResidentMedication::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ResidentMedicationNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;
            $physicianId = $params['physician_id'] ?? 0;
            $medicationId = $params['medication_id'] ?? 0;
            $formFactorId = $params['form_factor_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var Physician $physician */
            $physician = $this->em->getRepository(Physician::class)->getOne($currentSpace, $physicianId);

            if ($physician === null) {
                throw new PhysicianNotFoundException();
            }

            /** @var Medication $medication */
            $medication = $this->em->getRepository(Medication::class)->getOne($currentSpace, $medicationId);

            if ($medication === null) {
                throw new MedicationNotFoundException();
            }

            /** @var MedicationFormFactor $formFactor */
            $formFactor = $this->em->getRepository(MedicationFormFactor::class)->getOne($currentSpace, $formFactorId);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentMedication $entity */
            $entity = $this->em->getRepository(ResidentMedication::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentMedicationNotFoundException();
            }

            $residentMedications = $this->em->getRepository(ResidentMedication::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

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
}
