<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentMedicationAllergyNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use App\Entity\Resident;
use App\Entity\ResidentMedicationAllergy;
use App\Repository\MedicationRepository;
use App\Repository\ResidentMedicationAllergyRepository;
use App\Repository\ResidentRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rma.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentMedicationAllergyRepository $repo */
        $repo = $this->em->getRepository(ResidentMedicationAllergy::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentMedicationAllergyRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicationAllergy::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentMedicationAllergy|null|object
     */
    public function getById($id)
    {
        /** @var ResidentMedicationAllergyRepository $repo */
        $repo = $this->em->getRepository(ResidentMedicationAllergy::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $id);
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

            /** @var MedicationRepository $medicationRepo */
            $medicationRepo = $this->em->getRepository(Medication::class);

            /** @var Medication $medication */
            $medication = $medicationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Medication::class), $params['medication_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($medication === null) {
                throw new MedicationNotFoundException();
            }

            $residentMedicationAllergy = new ResidentMedicationAllergy();
            $residentMedicationAllergy->setResident($resident);
            $residentMedicationAllergy->setMedication($medication);
            $residentMedicationAllergy->setNotes($params['notes']);

            $this->validate($residentMedicationAllergy, null, ['api_admin_resident_medication_allergy_add']);

            $this->em->persist($residentMedicationAllergy);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentMedicationAllergy->getId();
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

            /** @var ResidentMedicationAllergyRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicationAllergy::class);

            /** @var ResidentMedicationAllergy $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $id);

            if ($entity === null) {
                throw new ResidentMedicationAllergyNotFoundException();
            }

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $params['resident_id']);

            /** @var MedicationRepository $medicationRepo */
            $medicationRepo = $this->em->getRepository(Medication::class);

            /** @var Medication $medication */
            $medication = $medicationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Medication::class), $params['medication_id']);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            if ($medication === null) {
                throw new MedicationNotFoundException();
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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentMedicationAllergyRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicationAllergy::class);

            /** @var ResidentMedicationAllergy $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentMedicationAllergyNotFoundException();
            }

            /** @var ResidentMedicationAllergyRepository $repo */
            $repo = $this->em->getRepository(ResidentMedicationAllergy::class);

            $residentMedicationAllergies = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentMedicationAllergy::class), $ids);

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
