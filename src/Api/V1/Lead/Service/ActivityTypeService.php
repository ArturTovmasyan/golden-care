<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\Lead\ActivityStatusNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\ActivityType;
use App\Repository\Lead\ActivityStatusRepository;
use App\Repository\Lead\ActivityTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ActivityTypeService
 * @package App\Api\V1\Admin\Service
 */
class ActivityTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ActivityTypeRepository $repo */
        $repo = $this->em->getRepository(ActivityType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $category = null;
        if (!empty($params) && !empty($params[0]['category'])) {
            $category = $params[0]['category'];
        }

        /** @var ActivityTypeRepository $repo */
        $repo = $this->em->getRepository(ActivityType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $category);
    }

    /**
     * @param $id
     * @return ActivityType|null|object
     */
    public function getById($id)
    {
        /** @var ActivityTypeRepository $repo */
        $repo = $this->em->getRepository(ActivityType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $id);
    }

    /**
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            /** @var ActivityStatusRepository $statusRepo */
            $statusRepo = $this->em->getRepository(ActivityStatus::class);

            /** @var ActivityStatus $status */
            $status = $statusRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $params['default_status_id']);

            if ($status === null) {
                throw new ActivityStatusNotFoundException();
            }

            $activityType = new ActivityType();
            $activityType->setTitle($params['title']);
            $activityType->setDefaultStatus($status);
            $activityType->setAssignTo($params['assign_to']);
            $activityType->setDueDate($params['due_date']);
            $activityType->setReminderDate($params['reminder_date']);
            $activityType->setCc($params['cc']);
            $activityType->setSms($params['sms']);
            $activityType->setFacility($params['facility']);
            $activityType->setContact($params['contact']);
            $activityType->setAmount($params['amount']);
            $activityType->setEditable($params['editable']);
            $activityType->setDeletable($params['deletable']);
            $activityType->setCategories($params['categories']);

            $this->validate($activityType, null, ['api_lead_activity_type_add']);

            $this->em->persist($activityType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $activityType->getId();
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
    public function edit($id, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var ActivityTypeRepository $repo */
            $repo = $this->em->getRepository(ActivityType::class);

            /** @var ActivityType $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $id);

            if ($entity === null) {
                throw new ActivityTypeNotFoundException();
            }

            /** @var ActivityStatusRepository $statusRepo */
            $statusRepo = $this->em->getRepository(ActivityStatus::class);

            /** @var ActivityStatus $status */
            $status = $statusRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $params['default_status_id']);

            if ($status === null) {
                throw new ActivityStatusNotFoundException();
            }

            $entity->setTitle($params['title']);
            $entity->setDefaultStatus($status);
            $entity->setAssignTo($params['assign_to']);
            $entity->setDueDate($params['due_date']);
            $entity->setReminderDate($params['reminder_date']);
            $entity->setCc($params['cc']);
            $entity->setSms($params['sms']);
            $entity->setFacility($params['facility']);
            $entity->setContact($params['contact']);
            $entity->setAmount($params['amount']);
            $entity->setEditable($params['editable']);
            $entity->setDeletable($params['deletable']);
            $entity->setCategories($params['categories']);

            $this->validate($entity, null, ['api_lead_activity_type_edit']);

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

            /** @var ActivityTypeRepository $repo */
            $repo = $this->em->getRepository(ActivityType::class);

            /** @var ActivityType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $id);

            if ($entity === null) {
                throw new ActivityTypeNotFoundException();
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
    public function removeBulk(array $ids)
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if (empty($ids)) {
                throw new ActivityTypeNotFoundException();
            }

            /** @var ActivityTypeRepository $repo */
            $repo = $this->em->getRepository(ActivityType::class);

            $activityTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $ids);

            if (empty($activityTypes)) {
                throw new ActivityTypeNotFoundException();
            }

            /**
             * @var ActivityType $activityType
             */
            foreach ($activityTypes as $activityType) {
                $this->em->remove($activityType);
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
            throw new ActivityTypeNotFoundException();
        }

        /** @var ActivityTypeRepository $repo */
        $repo = $this->em->getRepository(ActivityType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $ids);

        if (empty($entities)) {
            throw new ActivityTypeNotFoundException();
        }

        return $this->getRelatedData(ActivityType::class, $entities);
    }
}
