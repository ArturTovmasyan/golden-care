<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\NotificationNotFoundException;
use App\Api\V1\Common\Service\Exception\NotificationTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Notification;
use App\Entity\NotificationType;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\NotificationTypeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class NotificationService
 * @package App\Api\V1\Admin\Service
 */
class NotificationService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var NotificationRepository $repo */
        $repo = $this->em->getRepository(Notification::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Notification::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var NotificationRepository $repo */
        $repo = $this->em->getRepository(Notification::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Notification::class));
    }

    /**
     * @param $id
     * @return Notification|null|object
     */
    public function getById($id)
    {
        /** @var NotificationRepository $repo */
        $repo = $this->em->getRepository(Notification::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Notification::class), $id);
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

            $typeId = $params['type_id'] ?? 0;

            /** @var NotificationTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(NotificationType::class);

            /** @var NotificationType $type */
            $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $typeId);

            if ($type === null) {
                throw new NotificationTypeNotFoundException();
            }

            $schedule = !empty($params['schedule']) ? $params['schedule'] : [];
            $parameters = !empty($params['parameters']) ? $params['parameters'] : [];

            $notification = new Notification();
            $notification->setType($type);
            $notification->setSchedule($schedule);
            $notification->setParameters($parameters);

            if(!empty($params['users'])) {
                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                $userIds = array_unique($params['users']);
                $users = $userRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userIds);

                if (!empty($users)) {
                    $notification->setUsers($users);
                }
            }

            $this->validate($notification, null, ['api_admin_notification_add']);

            $this->em->persist($notification);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $notification->getId();
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

            /** @var NotificationRepository $repo */
            $repo = $this->em->getRepository(Notification::class);

            /** @var Notification $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Notification::class), $id);

            if ($entity === null) {
                throw new NotificationNotFoundException();
            }

            $typeId = $params['type_id'] ?? 0;

            /** @var NotificationTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(NotificationType::class);

            /** @var NotificationType $type */
            $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $typeId);

            if ($type === null) {
                throw new NotificationTypeNotFoundException();
            }

            $schedule = !empty($params['schedule']) ? $params['schedule'] : [];
            $parameters = !empty($params['parameters']) ? $params['parameters'] : [];

            $entity->setType($type);
            $entity->setSchedule($schedule);
            $entity->setParameters($parameters);

            $users = $entity->getUsers();
            foreach ($users as $user) {
                $entity->removeUser($user);
            }

            if(!empty($params['users'])) {
                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                $userIds = array_unique($params['users']);
                $users = $userRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userIds);

                if (!empty($users)) {
                    $entity->setUsers($users);
                }
            }

            $this->validate($entity, null, ['api_admin_notification_edit']);

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

            /** @var NotificationRepository $repo */
            $repo = $this->em->getRepository(Notification::class);

            /** @var Notification $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Notification::class), $id);

            if ($entity === null) {
                throw new NotificationNotFoundException();
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
                throw new NotificationNotFoundException();
            }

            /** @var NotificationRepository $repo */
            $repo = $this->em->getRepository(Notification::class);

            $notifications = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Notification::class), $ids);

            if (empty($notifications)) {
                throw new NotificationNotFoundException();
            }

            /**
             * @var Notification $notification
             */
            foreach ($notifications as $notification) {
                $this->em->remove($notification);
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
            throw new NotificationNotFoundException();
        }

        /** @var NotificationRepository $repo */
        $repo = $this->em->getRepository(Notification::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Notification::class), $ids);

        if (empty($entities)) {
            throw new NotificationNotFoundException();
        }

        return $this->getRelatedData(Notification::class, $entities);
    }
}
