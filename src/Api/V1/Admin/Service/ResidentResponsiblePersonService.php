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
use App\Entity\ResidentLedger;
use App\Entity\ResidentRent;
use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonRole;
use App\Repository\RelationshipRepository;
use App\Repository\ResidentLedgerRepository;
use App\Repository\ResidentRentRepository;
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
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
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
            $responsiblePersons = $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $residentId);

                if (!empty($responsiblePersons) && isset($params[0]['financially'])) {
                $financiallyResponsiblePersons = [];
                /** @var ResidentResponsiblePerson $responsiblePerson */
                foreach ($responsiblePersons as $responsiblePerson) {
                    if (!empty($responsiblePerson->getRoles())) {
                        /** @var ResponsiblePersonRole $role */
                        foreach ($responsiblePerson->getRoles() as $role) {
                            if ($role->isFinancially() === true) {
                                $financiallyResponsiblePersons[] = $responsiblePerson;
                                break;
                            }
                        }
                    }
                }

                return $financiallyResponsiblePersons;
            }
            return $responsiblePersons;
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
    public function add(array $params): ?int
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
            $residentResponsiblePerson->setSortOrder(0);
            $residentResponsiblePerson->setResident($resident);
            $residentResponsiblePerson->setResponsiblePerson($responsiblePerson);
            $residentResponsiblePerson->setRelationship($relationship);

            /* Roles - begin */
            if (!empty($params['roles'])) {
                /** @var ResponsiblePersonRoleRepository $responsiblePersonRoleRepo */
                $responsiblePersonRoleRepo = $this->em->getRepository(ResponsiblePersonRole::class);

                $roleIds = array_unique($params['roles']);
                $roles = $responsiblePersonRoleRepo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $roleIds);

                if (!empty($roles)) {
                    $residentResponsiblePerson->setRoles($roles);
                }
            }
            /* Roles - end */

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
    public function edit($id, array $params): void
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

            /* Roles - begin */
            $roles = $entity->getRoles();
            foreach ($roles as $role) {
                $entity->removeRole($role);
            }

            if (!empty($params['roles'])) {
                /** @var ResponsiblePersonRoleRepository $responsiblePersonRoleRepo */
                $responsiblePersonRoleRepo = $this->em->getRepository(ResponsiblePersonRole::class);

                $roleIds = array_unique($params['roles']);
                $roles = $responsiblePersonRoleRepo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResponsiblePersonRole::class), $roleIds);

                if (!empty($roles)) {
                    $entity->setRoles($roles);
                }
            }
            /* Roles - end */

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

            $residentId = $entity->getResident() ? $entity->getResident()->getId() : 0;

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);

            $rents = $rentRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null, $residentId);

            if (!empty($rents)) {
                /** @var ResidentRent $rent */
                foreach ($rents as $rent) {
                    if (!empty($rent->getSource())) {
                        $sources = $rent->getSource();
                        foreach ($sources as $key => $source) {
                            if (array_key_exists('responsible_person_id', $source) && $source['responsible_person_id'] === $id) {
                                $sources[$key]['responsible_person_id'] = '';
                                $changedSources = array_values($sources);
                                $rent->setSource($changedSources);
                            }
                        }

                        $this->em->persist($rent);
                    }
                }
            }

            /** @var ResidentLedgerRepository $ledgerRepo */
            $ledgerRepo = $this->em->getRepository(ResidentLedger::class);

            $ledgers = $ledgerRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null, $residentId);

            if (!empty($ledgers)) {
                /** @var ResidentLedger $ledger */
                foreach ($ledgers as $ledger) {
                    if (!empty($ledger->getSource())) {
                        $sources = $ledger->getSource();
                        foreach ($sources as $key => $source) {
                            if (array_key_exists('responsible_person_id', $source) && $source['responsible_person_id'] === $id) {
                                $sources[$key]['responsible_person_id'] = '';
                                $changedSources = array_values($sources);
                                $ledger->setSource($changedSources);
                            }
                        }

                        $this->em->persist($ledger);
                    }

                    if (!empty($ledger->getPrivatPaySource())) {
                        $privatPaySources = $ledger->getPrivatPaySource();
                        foreach ($privatPaySources as $key => $source) {
                            if ($source['responsible_person_id'] === $id) {
                                $privatPaySources[$key]['responsible_person_id'] = '';
                                $changedSources = array_values($privatPaySources);
                                $ledger->setPrivatPaySource($changedSources);
                            }
                        }

                        $this->em->persist($ledger);
                    }
                }
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

            $ids = array_map(function ($item) {
                return $item->getId();
            }, $residentResponsiblePersons);

            /** @var ResidentRentRepository $rentRepo */
            $rentRepo = $this->em->getRepository(ResidentRent::class);

            $rents = $rentRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null);

            if (!empty($rents)) {
                foreach ($ids as $id) {
                    /** @var ResidentRent $rent */
                    foreach ($rents as $rent) {
                        if (!empty($rent->getSource())) {
                            $sources = $rent->getSource();
                            foreach ($sources as $key => $source) {
                                if (array_key_exists('responsible_person_id', $source) && $source['responsible_person_id'] === $id) {
                                    $sources[$key]['responsible_person_id'] = '';
                                    $changedSources = array_values($sources);
                                    $rent->setSource($changedSources);
                                }
                            }

                            $this->em->persist($rent);
                        }
                    }
                }
            }

            /** @var ResidentLedgerRepository $ledgerRepo */
            $ledgerRepo = $this->em->getRepository(ResidentLedger::class);

            $ledgers = $ledgerRepo->getEntityWithSources($this->grantService->getCurrentSpace(), null);

            if (!empty($ledgers)) {
                foreach ($ids as $id) {
                    /** @var ResidentLedger $ledger */
                    foreach ($ledgers as $ledger) {
                        if (!empty($ledger->getSource())) {
                            $sources = $ledger->getSource();
                            foreach ($sources as $key => $source) {
                                if (array_key_exists('responsible_person_id', $source) && $source['responsible_person_id'] === $id) {
                                    $sources[$key]['responsible_person_id'] = '';
                                    $changedSources = array_values($sources);
                                    $ledger->setSource($changedSources);
                                }
                            }

                            $this->em->persist($ledger);
                        }

                        if (!empty($ledger->getPrivatPaySource())) {
                            $privatPaySources = $ledger->getPrivatPaySource();
                            foreach ($privatPaySources as $key => $source) {
                                if ($source['responsible_person_id'] === $id) {
                                    $privatPaySources[$key]['responsible_person_id'] = '';
                                    $changedSources = array_values($privatPaySources);
                                    $ledger->setPrivatPaySource($changedSources);
                                }
                            }

                            $this->em->persist($ledger);
                        }
                    }
                }
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

    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new ResidentResponsiblePersonNotFoundException();
        }

        /** @var ResidentResponsiblePersonRepository $repo */
        $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class), $ids);

        if (empty($entities)) {
            throw new ResidentResponsiblePersonNotFoundException();
        }

        return $this->getRelatedData(ResidentResponsiblePerson::class, $entities);
    }

    /**
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function reorder(array $params)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (!empty($params) && !empty($params['responsible_persons'])) {
                /** @var ResidentResponsiblePersonRepository $repo */
                $repo = $this->em->getRepository(ResidentResponsiblePerson::class);

                foreach ($params['responsible_persons'] as $idx => $value) {
                    /** @var ResidentResponsiblePerson $rp */
                    $rp = $repo->getOne(
                        $this->grantService->getCurrentSpace(),
                        $this->grantService->getCurrentUserEntityGrants(ResidentResponsiblePerson::class),
                        $value['id']
                    );

                    if ($rp !== null) {
                        $rp->setSortOrder($idx);
                        $this->em->persist($rp);
                    }
                }

            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}
