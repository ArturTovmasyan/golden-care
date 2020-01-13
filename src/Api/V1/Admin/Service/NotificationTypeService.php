<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\NotificationTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\NotificationType;
use App\Entity\Space;
use App\Repository\NotificationTypeRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class NotificationTypeService
 * @package App\Api\V1\Admin\Service
 */
class NotificationTypeService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        /** @var NotificationTypeRepository $repo */
        $repo = $this->em->getRepository(NotificationType::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var NotificationTypeRepository $repo */
        $repo = $this->em->getRepository(NotificationType::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(NotificationType::class));
    }

    /**
     * @param $id
     * @return NotificationType|null|object
     */
    public function getById($id)
    {
        /** @var NotificationTypeRepository $repo */
        $repo = $this->em->getRepository(NotificationType::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $id);
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

            $category = $params['category'] ? (int)$params['category'] : 0;

            $notificationType = new NotificationType();
            $notificationType->setCategory($category);
            $notificationType->setTitle($params['title']);
            $notificationType->setEmail($params['email']);
            $notificationType->setSms($params['sms']);
            $notificationType->setFacility($params['facility']);
            $notificationType->setApartment($params['apartment']);
            $notificationType->setRegion($params['region']);
            $notificationType->setEmailSubject($params['email_subject'] ?? '');
            $notificationType->setEmailMessage($params['email_message'] ?? '');
            $notificationType->setSmsSubject($params['sms_subject'] ?? '');
            $notificationType->setSmsMessage($params['sms_message'] ?? '');
            $notificationType->setSpace($space);

            $this->validate($notificationType, null, ['api_admin_notification_type_add']);

            $this->em->persist($notificationType);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $notificationType->getId();
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

            /** @var NotificationTypeRepository $repo */
            $repo = $this->em->getRepository(NotificationType::class);

            /** @var NotificationType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $id);

            if ($entity === null) {
                throw new NotificationTypeNotFoundException();
            }

            /** @var Space $space */
            $space = $this->getSpace($params['space_id']);

            if ($space === null) {
                throw new SpaceNotFoundException();
            }

            $category = $params['category'] ? (int)$params['category'] : 0;

            $entity->setCategory($category);
            $entity->setTitle($params['title']);
            $entity->setEmail($params['email']);
            $entity->setSms($params['sms']);
            $entity->setFacility($params['facility']);
            $entity->setApartment($params['apartment']);
            $entity->setRegion($params['region']);
            $entity->setEmailSubject($params['email_subject'] ?? '');
            $entity->setEmailMessage($params['email_message'] ?? '');
            $entity->setSmsSubject($params['sms_subject'] ?? '');
            $entity->setSmsMessage($params['sms_message'] ?? '');
            $entity->setSpace($space);

            $this->validate($entity, null, ['api_admin_notification_type_edit']);

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

            /** @var NotificationTypeRepository $repo */
            $repo = $this->em->getRepository(NotificationType::class);

            /** @var NotificationType $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $id);

            if ($entity === null) {
                throw new NotificationTypeNotFoundException();
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
                throw new NotificationTypeNotFoundException();
            }

            /** @var NotificationTypeRepository $repo */
            $repo = $this->em->getRepository(NotificationType::class);

            $notificationTypes = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $ids);

            if (empty($notificationTypes)) {
                throw new NotificationTypeNotFoundException();
            }

            /**
             * @var NotificationType $notificationType
             */
            foreach ($notificationTypes as $notificationType) {
                $this->em->remove($notificationType);
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
            throw new NotificationTypeNotFoundException();
        }

        /** @var NotificationTypeRepository $repo */
        $repo = $this->em->getRepository(NotificationType::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(NotificationType::class), $ids);

        if (empty($entities)) {
            throw new NotificationTypeNotFoundException();
        }

        return $this->getRelatedData(NotificationType::class, $entities);
    }
}
