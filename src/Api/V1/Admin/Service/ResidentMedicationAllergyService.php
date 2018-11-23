<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationNotSingleException;
use App\Api\V1\Common\Service\Exception\MedicationNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentMedicationAllergyNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use App\Entity\Resident;
use App\Entity\ResidentMedicationAllergy;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentMedicationAllergyService
 * @package App\Api\V1\Admin\Service
 */
class ResidentMedicationAllergyService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            $this->em->getRepository(ResidentMedicationAllergy::class)->findBy(['resident' => $residentId]);
        } else {
            $this->em->getRepository(ResidentMedicationAllergy::class)->search($queryBuilder);
        }
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentMedicationAllergy::class)->findBy(['resident' => $residentId]);
        }

        return $this->em->getRepository(ResidentMedicationAllergy::class)->findAll();
    }

    /**
     * @param $id
     * @return ResidentMedicationAllergy|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentMedicationAllergy::class)->find($id);
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

            $medicationId = $params['medication_id'];
            $newMedication = $params['medication'];

            if ((empty($medicationId) && empty($newMedication)) || (!empty($medicationId) && !empty($newMedication))) {
                throw new MedicationNotSingleException();
            }

            $medication = null;

            if (!empty($newMedication)) {
                $newMedicationName = $newMedication['name'] ?? '';

                $medication = new Medication();
                $medication->setName($newMedicationName);
            }

            if (!empty($medicationId)) {
                /** @var Medication $medication */
                $medication = $this->em->getRepository(Medication::class)->find($medicationId);

                if ($medication === null) {
                    throw new MedicationNotFoundException();
                }
            }

            $residentMedicationAllergy = new ResidentMedicationAllergy();
            $residentMedicationAllergy->setResident($resident);
            $residentMedicationAllergy->setMedication($medication);
            $residentMedicationAllergy->setNotes($params['notes']);

            $this->validate($residentMedicationAllergy, null, ['api_admin_resident_medication_allergy_add']);

            $this->em->persist($medication);
            $this->em->persist($residentMedicationAllergy);
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

            /** @var ResidentMedicationAllergy $entity */
            $entity = $this->em->getRepository(ResidentMedicationAllergy::class)->find($id);

            if ($entity === null) {
                throw new ResidentMedicationAllergyNotFoundException();
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

            $medicationId = $params['medication_id'];
            $newMedication = $params['medication'];

            if ((empty($medicationId) && empty($newMedication)) || (!empty($medicationId) && !empty($newMedication))) {
                throw new MedicationNotSingleException();
            }

            $medication = null;

            if (!empty($newMedication)) {
                $newMedicationName = $newMedication['name'] ?? '';

                $medication = new Medication();
                $medication->setName($newMedicationName);
            }

            if (!empty($medicationId)) {
                /** @var Medication $medication */
                $medication = $this->em->getRepository(Medication::class)->find($medicationId);

                if ($medication === null) {
                    throw new MedicationNotFoundException();
                }
            }

            $entity->setResident($resident);
            $entity->setMedication($medication);
            $entity->setNotes($params['notes']);

            $this->validate($entity, null, ['api_admin_resident_medication_allergy_edit']);

            $this->em->persist($medication);
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

            /** @var ResidentMedicationAllergy $entity */
            $entity = $this->em->getRepository(ResidentMedicationAllergy::class)->find($id);

            if ($entity === null) {
                throw new ResidentMedicationAllergyNotFoundException();
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
                throw new ResidentMedicationAllergyNotFoundException();
            }

            $residentMedicationAllergies = $this->em->getRepository(ResidentMedicationAllergy::class)->findByIds($ids);

            if (empty($residentMedicationAllergies)) {
                throw new ResidentMedicationAllergyNotFoundException();
            }

            /**
             * @var ResidentMedicationAllergy $residentMedicationAllergy
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentMedicationAllergies as $residentMedicationAllergy) {
                $this->em->remove($residentMedicationAllergy);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentMedicationAllergyNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
