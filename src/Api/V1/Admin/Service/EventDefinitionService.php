<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\EventDefinitionNotFoundException;
use App\Api\V1\Common\Service\Exception\NotAValidChoiceException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\EventDefinition;
use App\Entity\Space;
use App\Model\EventDefinitionView;
use App\Repository\EventDefinitionRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class EventDefinitionService
 * @package App\Api\V1\Admin\Service
 */
class EventDefinitionService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var EventDefinitionRepository $repo */
        $repo = $this->em->getRepository(EventDefinition::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var EventDefinitionRepository $repo */
        $repo = $this->em->getRepository(EventDefinition::class);

        $view = null;
        if (!empty($params) && !empty($params[0]['view'])) {
            $view = (int)$params[0]['view'];
        }

        $type = null;
        if (!empty($params) && !empty($params[0]['type'])) {
            $type = (int)$params[0]['type'];
        }

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $view, $type);
    }

    /**
     * @param $id
     * @return EventDefinition|null|object
     */
    public function getById($id)
    {
        /** @var EventDefinitionRepository $repo */
        $repo = $this->em->getRepository(EventDefinition::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $type = $params['type'] ? (int)$params['type'] : 0;
            $view = $params['view'] ? (int)$params['view'] : 0;

            $entity = new EventDefinition();
            $entity->setSpace($space);
            $entity->setType($type);
            $entity->setView($view);
            $entity->setTitle($params['title']);
            $entity->setInChooser($params['in_chooser']);

            switch ($view) {
                case EventDefinitionView::RESIDENT:
                    $entity->setFfc($params['ffc']);
                    $entity->setIhc($params['ihc']);
                    $entity->setIl($params['il']);
                    $entity->setPhysician($params['physician']);
                    $entity->setPhysicianOptional($params['physician_optional']);
                    $entity->setResponsiblePerson($params['responsible_person']);
                    $entity->setResponsiblePersonOptional($params['responsible_person_optional']);
                    $entity->setResponsiblePersonMulti($params['responsible_person_multi']);
                    $entity->setResponsiblePersonMultiOptional($params['responsible_person_multi_optional']);
                    $entity->setHospiceProvider($params['hospice_provider']);
                    $entity->setAdditionalDate($params['additional_date']);

                    $entity->setResidents(false);
                    $entity->setUsers(false);
                    $entity->setDuration(false);
                    $entity->setRepeats(false);
                    $entity->setRsvp(false);
                    $entity->setDone(false);
                    break;
                case EventDefinitionView::FACILITY:
                    $entity->setFfc(false);
                    $entity->setIhc(false);
                    $entity->setIl(false);
                    $entity->setPhysician(false);
                    $entity->setPhysicianOptional(false);
                    $entity->setResponsiblePerson(false);
                    $entity->setResponsiblePersonOptional(false);
                    $entity->setResponsiblePersonMulti(false);
                    $entity->setResponsiblePersonMultiOptional(false);
                    $entity->setHospiceProvider(false);
                    $entity->setAdditionalDate(false);

                    $entity->setResidents($params['residents']);
                    $entity->setUsers($params['users']);
                    $entity->setDuration($params['duration']);
                    $entity->setRepeats($params['repeats']);
                    $entity->setRsvp($params['rsvp']);

                    $entity->setDone(false);
                    break;
                case EventDefinitionView::CORPORATE:
                    $entity->setFfc(false);
                    $entity->setIhc(false);
                    $entity->setIl(false);
                    $entity->setPhysician(false);
                    $entity->setPhysicianOptional(false);
                    $entity->setResponsiblePerson(false);
                    $entity->setResponsiblePersonOptional(false);
                    $entity->setResponsiblePersonMulti(false);
                    $entity->setResponsiblePersonMultiOptional(false);
                    $entity->setHospiceProvider(false);
                    $entity->setAdditionalDate(false);
                    $entity->setResidents(false);

                    $entity->setUsers($params['users']);
                    $entity->setDuration($params['duration']);
                    $entity->setRepeats($params['repeats']);
                    $entity->setRsvp($params['rsvp']);
                    $entity->setDone($params['done']);
                    break;
                default:
                    throw new NotAValidChoiceException();
            }

            $this->validate($entity, null, ['api_admin_event_definition_add']);

            $this->em->persist($entity);

            if ($entity->getView() === EventDefinitionView::CORPORATE && $entity->isDone()) {
                $entity->setUsers(true);
            }

            $this->em->persist($entity);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $entity->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var EventDefinitionRepository $repo */
            $repo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $id);

            if ($entity === null) {
                throw new EventDefinitionNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $type = $params['type'] ? (int)$params['type'] : 0;
            $view = $params['view'] ? (int)$params['view'] : 0;

            $entity->setSpace($space);
            $entity->setType($type);
            $entity->setView($view);
            $entity->setTitle($params['title']);
            $entity->setInChooser($params['in_chooser']);

            switch ($view) {
                case EventDefinitionView::RESIDENT:
                    $entity->setFfc($params['ffc']);
                    $entity->setIhc($params['ihc']);
                    $entity->setIl($params['il']);
                    $entity->setPhysician($params['physician']);
                    $entity->setPhysicianOptional($params['physician_optional']);
                    $entity->setResponsiblePerson($params['responsible_person']);
                    $entity->setResponsiblePersonOptional($params['responsible_person_optional']);
                    $entity->setResponsiblePersonMulti($params['responsible_person_multi']);
                    $entity->setResponsiblePersonMultiOptional($params['responsible_person_multi_optional']);
                    $entity->setHospiceProvider($params['hospice_provider']);
                    $entity->setAdditionalDate($params['additional_date']);

                    $entity->setResidents(false);
                    $entity->setUsers(false);
                    $entity->setDuration(false);
                    $entity->setRepeats(false);
                    $entity->setRsvp(false);
                    $entity->setDone(false);
                    break;
                case EventDefinitionView::FACILITY:
                    $entity->setFfc(false);
                    $entity->setIhc(false);
                    $entity->setIl(false);
                    $entity->setPhysician(false);
                    $entity->setPhysicianOptional(false);
                    $entity->setResponsiblePerson(false);
                    $entity->setResponsiblePersonOptional(false);
                    $entity->setResponsiblePersonMulti(false);
                    $entity->setResponsiblePersonMultiOptional(false);
                    $entity->setHospiceProvider(false);
                    $entity->setAdditionalDate(false);

                    $entity->setResidents($params['residents']);
                    $entity->setUsers($params['users']);
                    $entity->setDuration($params['duration']);
                    $entity->setRepeats($params['repeats']);
                    $entity->setRsvp($params['rsvp']);

                    $entity->setDone(false);
                    break;
                case EventDefinitionView::CORPORATE:
                    $entity->setFfc(false);
                    $entity->setIhc(false);
                    $entity->setIl(false);
                    $entity->setPhysician(false);
                    $entity->setPhysicianOptional(false);
                    $entity->setResponsiblePerson(false);
                    $entity->setResponsiblePersonOptional(false);
                    $entity->setResponsiblePersonMulti(false);
                    $entity->setResponsiblePersonMultiOptional(false);
                    $entity->setHospiceProvider(false);
                    $entity->setAdditionalDate(false);
                    $entity->setResidents(false);

                    $entity->setUsers($params['users']);
                    $entity->setDuration($params['duration']);
                    $entity->setRepeats($params['repeats']);
                    $entity->setRsvp($params['rsvp']);
                    $entity->setDone($params['done']);
                    break;
                default:
                    throw new NotAValidChoiceException();
            }

            $this->validate($entity, null, ['api_admin_event_definition_edit']);

            $this->em->persist($entity);

            if ($entity->getView() === EventDefinitionView::CORPORATE && $entity->isDone()) {
                $entity->setUsers(true);
            }

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

            /** @var EventDefinitionRepository $repo */
            $repo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $id);

            if ($entity === null) {
                throw new EventDefinitionNotFoundException();
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
                throw new EventDefinitionNotFoundException();
            }

            /** @var EventDefinitionRepository $repo */
            $repo = $this->em->getRepository(EventDefinition::class);

            $eventDefinitions = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $ids);

            if (empty($eventDefinitions)) {
                throw new EventDefinitionNotFoundException();
            }

            /**
             * @var EventDefinition $eventDefinition
             */
            foreach ($eventDefinitions as $eventDefinition) {
                $this->em->remove($eventDefinition);
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
            throw new EventDefinitionNotFoundException();
        }

        /** @var EventDefinitionRepository $repo */
        $repo = $this->em->getRepository(EventDefinition::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $ids);

        if (empty($entities)) {
            throw new EventDefinitionNotFoundException();
        }

        return $this->getRelatedData(EventDefinition::class, $entities);
    }
}
