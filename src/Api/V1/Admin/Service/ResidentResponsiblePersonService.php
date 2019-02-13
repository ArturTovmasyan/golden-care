<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Relationship;
use App\Entity\Resident;
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
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rrp.resident = :residentId')
            ->setParameter('residentId', $residentId);

        $this->em->getRepository(ResidentResponsiblePerson::class)->search($this->grantService->getCurrentSpace(), $queryBuilder);
    }

    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            return $this->em->getRepository(ResidentResponsiblePerson::class)->getBy($this->grantService->getCurrentSpace(), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentResponsiblePerson|null|object
     */
    public function getById($id)
    {
        return $this->em->getRepository(ResidentResponsiblePerson::class)->getOne($this->grantService->getCurrentSpace(), $id);
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

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId          = $params['resident_id'] ?? 0;
            $responsiblePersonId = $params['responsible_person_id'] ?? 0;
            $relationshipId      = $params['relationship_id'] ?? 0;

            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $responsiblePerson = $this->em->getRepository(ResponsiblePerson::class)->getOne($currentSpace, $responsiblePersonId);

            if ($responsiblePerson === null) {
                throw new ResponsiblePersonNotFoundException();
            }

            $relationship = $this->em->getRepository(Relationship::class)->getOne($currentSpace, $relationshipId);

            if ($relationship === null) {
                throw new RelationshipNotFoundException();
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
             * @var ResidentResponsiblePerson $entity
             * @var Resident $resident
             * @var ResponsiblePerson $responsiblePerson
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $entity = $this->em->getRepository(ResidentResponsiblePerson::class)->getOne($currentSpace, $id);

            if ($entity === null) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            $residentId          = $params['resident_id'] ?? 0;
            $responsiblePersonId = $params['responsible_person_id'] ?? 0;
            $relationshipId      = $params['relationship_id'] ?? 0;

            $resident = $this->em->getRepository(Resident::class)->getOne($currentSpace, $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $responsiblePerson = $this->em->getRepository(ResponsiblePerson::class)->getOne($currentSpace, $responsiblePersonId);

            if ($responsiblePerson === null) {
                throw new ResponsiblePersonNotFoundException();
            }

            $relationship = $this->em->getRepository(Relationship::class)->getOne($currentSpace, $relationshipId);

            if ($relationship === null) {
                throw new RelationshipNotFoundException();
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
            $entity = $this->em->getRepository(ResidentResponsiblePerson::class)->getOne($this->grantService->getCurrentSpace(), $id);

            if ($entity === null) {
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

            $residentResponsiblePersons = $this->em->getRepository(ResidentResponsiblePerson::class)->findByIds($this->grantService->getCurrentSpace(), $ids);

            if (empty($residentResponsiblePersons)) {
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
