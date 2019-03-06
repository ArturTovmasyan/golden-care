<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\RelationshipNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonNotFoundException;
use App\Api\V1\Common\Service\Exception\ResponsiblePersonRoleNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Relationship;
use App\Entity\Resident;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonRole;
use App\Repository\RelationshipRepository;
use App\Repository\ResidentRepository;
use App\Repository\ResidentResponsiblePersonRepository;
use App\Repository\ResponsiblePersonRepository;
use App\Repository\ResponsiblePersonRoleRepository;
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
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        if (empty($params) || empty($params[0]['resident_id'])) {
            throw new ResidentNotFoundException();
        }

        $residentId = $params[0]['resident_id'];

        $queryBuilder
            ->where('rrp.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentResponsiblePersonRepository $repo */
        $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentResponsiblePersonRepository $repo */
            $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentResponsiblePerson|null|object
     */
    public function getById($id)
    {
        /** @var ResidentResponsiblePersonRepository $repo */
        $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $id);
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
            /**
             * @var Resident $resident
             * @var ResponsiblePerson $responsiblePerson
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $residentId = $params['resident_id'] ?? 0;
            $responsiblePersonId = $params['responsible_person_id'] ?? 0;
            $relationshipId = $params['relationship_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var ResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository(ResponsiblePerson::class);

            $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $responsiblePersonId);

            if ($responsiblePerson === null) {
                throw new ResponsiblePersonNotFoundException();
            }

            /** @var RelationshipRepository $relationshipRepo */
            $relationshipRepo = $this->em->getRepository(Relationship::class);

            $relationship = $relationshipRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Relationship::class), $relationshipId);

            if ($relationship === null) {
                throw new RelationshipNotFoundException();
            }

            $residentResponsiblePerson = new ResidentResponsiblePerson();
            $residentResponsiblePerson->setResident($resident);
            $residentResponsiblePerson->setResponsiblePerson($responsiblePerson);
            $residentResponsiblePerson->setRelationship($relationship);

            if($params['role_id'] !== null) {
                /** @var ResponsiblePersonRoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(ResponsiblePersonRole::class);

                $role = $roleRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $params['role_id']);

                if ($role === null) {
                    throw new ResponsiblePersonRoleNotFoundException();
                }
                $residentResponsiblePerson->setRole($role);
            }

            $this->validate($residentResponsiblePerson, null, ['api_admin_resident_responsible_person_add']);

            $this->em->persist($residentResponsiblePerson);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentResponsiblePerson->getId();
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
            /**
             * @var ResidentResponsiblePerson $entity
             * @var Resident $resident
             * @var ResponsiblePerson $responsiblePerson
             * @var Relationship $relationship
             */
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ResidentResponsiblePersonRepository $repo */
            $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $id);

            if ($entity === null) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;
            $responsiblePersonId = $params['responsible_person_id'] ?? 0;
            $relationshipId = $params['relationship_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            /** @var ResponsiblePersonRepository $responsiblePersonRepo */
            $responsiblePersonRepo = $this->em->getRepository(ResponsiblePerson::class);

            $responsiblePerson = $responsiblePersonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class), $responsiblePersonId);

            if ($responsiblePerson === null) {
                throw new ResponsiblePersonNotFoundException();
            }

            /** @var RelationshipRepository $relationshipRepo */
            $relationshipRepo = $this->em->getRepository(Relationship::class);

            $relationship = $relationshipRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Relationship::class), $relationshipId);

            if ($relationship === null) {
                throw new RelationshipNotFoundException();
            }

            $entity->setResident($resident);
            $entity->setResponsiblePerson($responsiblePerson);
            $entity->setRelationship($relationship);

            if($params['role_id'] !== null) {
                /** @var ResponsiblePersonRoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(ResponsiblePersonRole::class);

                $role = $roleRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $params['role_id']);

                if ($role === null) {
                    throw new ResponsiblePersonRoleNotFoundException();
                }
                $entity->setRole($role);
            }

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
     * @throws \Throwable
     */
    public function remove($id)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ResidentResponsiblePersonRepository $repo */
            $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

            /** @var ResidentResponsiblePerson $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $id);

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
     * @throws \Throwable
     */
    public function removeBulk(array $ids): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            /** @var ResidentResponsiblePersonRepository $repo */
            $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

            $residentResponsiblePersons = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $ids);

            if (empty($residentResponsiblePersons)) {
                throw new ResidentResponsiblePersonNotFoundException();
            }

            /**
             * @var $residentResponsiblePerson $residentResponsiblePerson
             */
            foreach ($residentResponsiblePersons as $residentResponsiblePerson) {
                $this->em->remove($residentResponsiblePerson);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
