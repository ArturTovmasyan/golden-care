<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AdditionalDateNotBeBlankException;
use App\Api\V1\Common\Service\Exception\EventDefinitionNotFoundException;
use App\Api\V1\Common\Service\Exception\PhysicianNotBeBlankException;
use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentEventNotFoundException;
use App\Api\V1\Common\Service\Exception\ResidentNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\EventDefinition;
use App\Entity\Physician;
use App\Entity\Resident;
use App\Entity\ResidentEvent;
use App\Entity\ResponsiblePerson;
use App\Repository\EventDefinitionRepository;
use App\Repository\PhysicianRepository;
use App\Repository\ResidentEventRepository;
use App\Repository\ResidentRepository;
use App\Repository\ResponsiblePersonRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ResidentEventService
 * @package App\Api\V1\Admin\Service
 */
class ResidentEventService extends BaseService implements IGridService
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
            ->where('re.resident = :residentId')
            ->setParameter('residentId', $residentId);

        /** @var ResidentEventRepository $repo */
        $repo = $this->em->getRepository(ResidentEvent::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        if (!empty($params) && !empty($params[0]['resident_id'])) {
            $residentId = $params[0]['resident_id'];

            /** @var ResidentEventRepository $repo */
            $repo = $this->em->getRepository(ResidentEvent::class);

            return $repo->getBy($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $residentId);
        }

        throw new ResidentNotFoundException();
    }

    /**
     * @param $id
     * @return ResidentEvent|null|object
     */
    public function getById($id)
    {
        /** @var ResidentEventRepository $repo */
        $repo = $this->em->getRepository(ResidentEvent::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $id);
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

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $definitionId = $params['definition_id'] ?? 0;

            /** @var EventDefinitionRepository $definitionRepo */
            $definitionRepo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $definition */
            $definition = $definitionRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $definitionId);

            if ($definition === null) {
                throw new EventDefinitionNotFoundException();
            }

            $physician = null;

            if ($definition && ($definition->isPhysician() || $definition->isPhysicianOptional())) {
                $physicianId = (int)$params['physician_id'];

                if ($physicianId) {
                    /** @var PhysicianRepository $physicianRepo */
                    $physicianRepo = $this->em->getRepository(Physician::class);

                    /** @var Physician $physician */
                    $physician = $physicianRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Physician::class), $physicianId);

                    if ($physician === null) {
                        throw new PhysicianNotFoundException();
                    }
                } else {
                    throw new PhysicianNotBeBlankException();
                }
            }

            $rps = [];

            if ($definition && ($definition->isResponsiblePerson() || $definition->isResponsiblePersonMulti() || $definition->isResponsiblePersonOptional() || $definition->isResponsiblePersonMultiOptional())) {
                /** @var ResponsiblePersonRepository $rpRepo */
                $rpRepo = $this->em->getRepository(ResponsiblePerson::class);

                $rpIds = array_unique($params['responsible_persons']);
                $rps = $rpRepo->findByIds(
                    $this->grantService->getCurrentSpace(),
                    $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class),
                    $rpIds
                );
            }

            $additionalDate = null;

            if ($definition && $definition->isAdditionalDate()) {

                if (!empty($params['additional_date'])) {
                    $additionalDate = new \DateTime($params['additional_date']);
                } else {
                    throw new AdditionalDateNotBeBlankException();
                }
            }

            $residentEvent = new ResidentEvent();
            $residentEvent->setResident($resident);
            $residentEvent->setDefinition($definition);
            $residentEvent->setPhysician($physician);

            if (!empty($rps)) {
                $residentEvent->setResponsiblePersons($rps);
            }

            $residentEvent->setAdditionalDate($additionalDate);
            $residentEvent->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $residentEvent->setDate($date);

            $this->validate($residentEvent, null, ['api_admin_resident_event_add']);

            $this->em->persist($residentEvent);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $residentEvent->getId();
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

            /** @var ResidentEventRepository $repo */
            $repo = $this->em->getRepository(ResidentEvent::class);

            /** @var ResidentEvent $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $id);

            if ($entity === null) {
                throw new ResidentEventNotFoundException();
            }

            $residentId = $params['resident_id'] ?? 0;

            /** @var ResidentRepository $residentRepo */
            $residentRepo = $this->em->getRepository(Resident::class);

            /** @var Resident $resident */
            $resident = $residentRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Resident::class), $residentId);

            if ($resident === null) {
                throw new ResidentNotFoundException();
            }

            $definition = $entity->getDefinition();

            $physician = null;

            if ($definition && ($definition->isPhysician() || $definition->isPhysicianOptional())) {
                $physicianId = (int)$params['physician_id'];

                if ($physicianId) {
                    /** @var PhysicianRepository $physicianRepo */
                    $physicianRepo = $this->em->getRepository(Physician::class);

                    /** @var Physician $physician */
                    $physician = $physicianRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Physician::class), $physicianId);

                    if ($physician === null) {
                        throw new PhysicianNotFoundException();
                    }
                } else {
                    throw new PhysicianNotBeBlankException();
                }
            }

            $rps = [];
            if ($definition && ($definition->isResponsiblePerson() || $definition->isResponsiblePersonMulti() || $definition->isResponsiblePersonOptional() || $definition->isResponsiblePersonMultiOptional())) {
                $oldRPs = $entity->getResponsiblePersons();
                foreach ($oldRPs as $oldRP) {
                    $entity->removeResponsiblePerson($oldRP);
                }

                /** @var ResponsiblePersonRepository $rpRepo */
                $rpRepo = $this->em->getRepository(ResponsiblePerson::class);

                $rpIds = array_unique($params['responsible_persons']);
                $rps = $rpRepo->findByIds(
                    $this->grantService->getCurrentSpace(),
                    $this->grantService->getCurrentUserEntityGrants(ResponsiblePerson::class),
                    $rpIds
                );
            }

            $additionalDate = null;

            if ($definition && $definition->isAdditionalDate()) {

                if (!empty($params['additional_date'])) {
                    $additionalDate = new \DateTime($params['additional_date']);
                } else {
                    throw new AdditionalDateNotBeBlankException();
                }
            }

            $entity->setResident($resident);
            $entity->setPhysician($physician);

            if (!empty($rps)) {
                $entity->setResponsiblePersons($rps);
            }

            $entity->setAdditionalDate($additionalDate);
            $entity->setNotes($params['notes']);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            $this->validate($entity, null, ['api_admin_resident_event_edit']);

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

            /** @var ResidentEventRepository $repo */
            $repo = $this->em->getRepository(ResidentEvent::class);

            /** @var ResidentEvent $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $id);

            if ($entity === null) {
                throw new ResidentEventNotFoundException();
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
                throw new ResidentEventNotFoundException();
            }

            /** @var ResidentEventRepository $repo */
            $repo = $this->em->getRepository(ResidentEvent::class);

            $residentEvents = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $ids);

            if (empty($residentEvents)) {
                throw new ResidentEventNotFoundException();
            }

            /**
             * @var ResidentEvent $residentEvent
             */
            foreach ($residentEvents as $residentEvent) {
                $this->em->remove($residentEvent);
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
            throw new ResidentEventNotFoundException();
        }

        /** @var ResidentEventRepository $repo */
        $repo = $this->em->getRepository(ResidentEvent::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ResidentEvent::class), $ids);

        if (empty($entities)) {
            throw new ResidentEventNotFoundException();
        }

        return $this->getRelatedData(ResidentEvent::class, $entities);
    }
}
