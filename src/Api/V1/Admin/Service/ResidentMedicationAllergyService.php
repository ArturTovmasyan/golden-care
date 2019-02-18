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
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rma.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentMedicationAllergy::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentMedicationAllergy::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentMedicationAllergy|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentMedicationAllergy::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $medicationId = $params['medication_id'];
            $newMedication = $params['medication'];

            if ((empty($medicationId) && empty($newMedication)) || (!empty($medicationId) && !empty($newMedication))) {
                throw new MedicationNotSingleException();
            }

            $medication = null;

            if (!empty($newMedication)) {
                $newMedicationTitle = $newMedication['title'] ?? '';

                $medication = new Medication();
                $medication->setTitle($newMedicationTitle);
                $medication->setSpace($resident->getSpace());
            }

            if (!empty($medicationId)) {
                /** @var Medication $medication */
                $medication = $this->em->getRepository(Medication::class)->getOne($currentSpace, $medicationId);

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

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentMedicationAllergy $entity */
            $entity = $this->em->getRepository(ResidentMedicationAllergy::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ResidentMedicationAllergyNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var Resident $resident */
            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $medicationId = $params['medication_id'];
            $newMedication = $params['medication'];

            if ((empty($medicationId) && empty($newMedication)) || (!empty($medicationId) && !empty($newMedication))) {
                throw new MedicationNotSingleException();
            }

            $medication = null;

            if (!empty($newMedication)) {
                $newMedicationTitle = $newMedication['title'] ?? '';

                $medication = new Medication();
                $medication->setTitle($newMedicationTitle);
                $medication->setSpace($resident->getSpace());
            }

            if (!empty($medicationId)) {
                /** @var Medication $medication */
                $medication = $this->em->getRepository(Medication::class)->getOne($currentSpace, $medicationId);

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
            $entity = $this->em->getRepository(ResidentMedicationAllergy::class)->getOne($this->grantService->getCurrentSpace(), $id);

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
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentMedicationAllergyNotFoundException();
            }

            $residentMedicationAllergies = $this->em->getRepository(ResidentMedicationAllergy::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($residentMedicationAllergies)) {
                throw new ResidentMedicationAllergyNotFoundException();
            }

            /**
             * @var ResidentMedicationAllergy $residentMedicationAllergy
             */
            foreach ($residentMedicationAllergies as $residentMedicationAllergy) {
                $this->em->remove($residentMedicationAllergy);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
