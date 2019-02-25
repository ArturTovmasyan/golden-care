<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\EventDefinitionNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\EventDefinition;
use App\Entity\Space;
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
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
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

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class));
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
     * @throws \Exception
     */
    public function add(array $params) : void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->getSpace($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $eventDefinition = new EventDefinition();
            $eventDefinition->setTitle($params['title']);
            $eventDefinition->setFfc($params['ffc']);
            $eventDefinition->setIhc($params['ihc']);
            $eventDefinition->setIl($params['il']);
            $eventDefinition->setPhysician($params['physician']);
            $eventDefinition->setResponsiblePerson($params['responsible_person']);
            $eventDefinition->setAdditionalDate($params['additional_date']);
            $eventDefinition->setSpace($space);

            $this->validate($eventDefinition, null, ['api_admin_event_definition_add']);

            $this->em->persist($eventDefinition);
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

            /** @var EventDefinitionRepository $repo */
            $repo = $this->em->getRepository(EventDefinition::class);

            /** @var EventDefinition $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(EventDefinition::class), $id);

            if ($entity === null) {
                throw new EventDefinitionNotFoundException();
            }

            $spaceId = $params['space_id'] ?? 0;

            /** @var Space $space */
            $space = $this->getSpace($spaceId);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setFfc($params['ffc']);
            $entity->setIhc($params['ihc']);
            $entity->setIl($params['il']);
            $entity->setPhysician($params['physician']);
            $entity->setResponsiblePerson($params['responsible_person']);
            $entity->setAdditionalDate($params['additional_date']);
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_event_definition_edit']);

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
}
