<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\MedicationNotSingleException;
use App\Api\V1\Common\Service\Exception\MedicationNotFoundException;
use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentMedicationAllergyNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use App\Entity\Relationship;
use App\Entity\Resident;
use App\Entity\ResidentMedicationAllergy;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentResponsiblePersonService
 * @package App\Api\V1\Admin\Service
 */
class ResidentResponsiblePersonService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return void
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params)
    {
        $residentId = false;

        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];
        }

        $this->em->getRepository(ResidentResponsiblePerson::class)->search($queryBuilder, $residentId);
    }

    /**
     * @param $params
     * @return array|object[]
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentResponsiblePerson::class)->findBy(['resident' => $residentId]);
        }

        return $this->em->getRepository(ResidentResponsiblePerson::class)->findAll();
    }

    /**
     * @param $id
     * @return ResidentResponsiblePerson|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentResponsiblePerson::class)->find($id);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            /**
             * @var Resident $resident
             * @var ResponsiblePerson $responsiblePerson
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $residentId          = $params['resident_id'] ?? 0;
            $responsiblePersonId = $params['responsible_person_id'];
            $relationshipId      = $params['relationship_id'];
            $resident            = null;
            $responsiblePerson   = null;
            $relationship        = null;

            if ($residentId && $residentId > 0) {
                $resident = $this->em->getRepository(Resident::class)->find($residentId);

                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            if ($responsiblePersonId && $responsiblePersonId > 0) {
                $responsiblePerson = $this->em->getRepository(ResponsiblePerson::class)->find($responsiblePersonId);

                if (is_null($responsiblePerson)) {
                    throw new ResponsiblePersonNotFoundException();
                }
            }

            if (!empty($relationshipId)) {
                $relationship = $this->em->getRepository(Relationship::class)->find($relationshipId);

                if (is_null($relationship)) {
                    throw new RelationshipNotFoundException();
                }
            }

            $residentResponsiblePerson = new ResidentResponsiblePerson();
            $residentResponsiblePerson->setResident($resident);
            $residentResponsiblePerson->setResponsiblePerson($responsiblePerson);
            $residentResponsiblePerson->setRelationship($relationship);

            $this->validate($residentResponsiblePerson, null, ['api_admin_resident_responsible_person_add']);

            $this->em->persist($residentResponsiblePerson);
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
            /**
             * @var ResidentMedicationAllergy $entity
             * @var Resident $resident
             * @var ResponsiblePerson $responsiblePerson
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $entity = $this->em->getRepository(ResidentResponsiblePerson::class)->find($id);

            if (is_null($entity)) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            $residentId          = $params['resident_id'] ?? 0;
            $responsiblePersonId = $params['responsible_person_id'];
            $relationshipId      = $params['relationship_id'];
            $resident            = null;
            $responsiblePerson   = null;
            $relationship        = null;

            if ($residentId && $residentId > 0) {
                $resident = $this->em->getRepository(Resident::class)->find($residentId);

                if ($resident === null) {
                    throw new ResidentNotFoundException();
                }
            }

            if ($responsiblePersonId && $responsiblePersonId > 0) {
                $responsiblePerson = $this->em->getRepository(ResponsiblePerson::class)->find($responsiblePersonId);

                if (is_null($responsiblePerson)) {
                    throw new ResponsiblePersonNotFoundException();
                }
            }

            if ($relationshipId && $relationshipId > 0) {
                $relationship = $this->em->getRepository(Relationship::class)->find($relationshipId);

                if (is_null($relationship)) {
                    throw new RelationshipNotFoundException();
                }
            }

            $entity->setResident($resident);
            $entity->setResponsiblePerson($responsiblePerson);
            $entity->setRelationship($relationship);

            $this->validate($entity, null, ['api_admin_resident_responsible_person_edit']);

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

            /** @var ResidentResponsiblePerson $entity */
            $entity = $this->em->getRepository(ResidentResponsiblePerson::class)->find($id);

            if (is_null($entity)) {
                throw new ResidentResponsiblePersonNotFoundException();
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
                throw new ResidentResponsiblePersonNotFoundException();
            }

            $residentResponsiblePersons = $this->em->getRepository(ResidentResponsiblePerson::class)->findByIds($ids);

            if (empty($residentMedicationAllergies)) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            /**
             * @var $residentResponsiblePerson $residentResponsiblePerson
             */
            $this->em->getConnection()->beginTransaction();

            foreach ($residentResponsiblePersons as $residentResponsiblePerson) {
                $this->em->remove($residentResponsiblePerson);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (ResidentResponsiblePersonNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
