<?php
namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\NotificationNotFoundException;
use App\Api\V1\Common\Service\Exception\NotificationTypeNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Apartment;
use App\Entity\Facility;
use App\Entity\Notification;
use App\Entity\NotificationType;
use App\Entity\Region;
use App\Entity\User;
use App\Repository\ApartmentRepository;
use App\Repository\FacilityRepository;
use App\Repository\NotificationRepository;
use App\Repository\NotificationTypeRepository;
use App\Repository\RegionRepository;
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

            $emails = !empty($params['emails']) ? $params['emails'] : [];

            $notification = new Notification();
            $notification->setType($type);
            $notification->setEnabled($params['enabled']);
            $notification->setSchedule($params['schedule'] ?? '');
            $notification->setEmails($emails);

            if(!empty($params['users'])) {
                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                $userIds = array_unique($params['users']);
                $users = $userRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userIds);

                if (!empty($users)) {
                    $notification->setUsers($users);
                }
            }

            if ($type->isFacility()) {
                if(!empty($params['facilities'])) {
                    /** @var FacilityRepository $facilityRepo */
                    $facilityRepo = $this->em->getRepository(Facility::class);

                    $facilityIds = array_unique($params['facilities']);
                    $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                    if (!empty($facilities)) {
                        $notification->setFacilities($facilities);
                    }
                }
            } else {
                $notification->setFacilities(null);
            }

            if ($type->isApartment()) {
                if(!empty($params['apartments'])) {
                    /** @var ApartmentRepository $apartmentRepo */
                    $apartmentRepo = $this->em->getRepository(Apartment::class);

                    $apartmentIds = array_unique($params['apartments']);
                    $apartments = $apartmentRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class), $apartmentIds);

                    if (!empty($apartments)) {
                        $notification->setApartments($apartments);
                    }
                }
            } else {
                $notification->setApartments(null);
            }

            if ($type->isRegion()) {
                if(!empty($params['regions'])) {
                    /** @var RegionRepository $regionRepo */
                    $regionRepo = $this->em->getRepository(Region::class);

                    $regionIds = array_unique($params['regions']);
                    $regions = $regionRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class), $regionIds);

                    if (!empty($regions)) {
                        $notification->setRegions($regions);
                    }
                }
            } else {
                $notification->setRegions(null);
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

            $emails = !empty($params['emails']) ? $params['emails'] : [];

            $entity->setType($type);
            $entity->setEnabled($params['enabled']);
            $entity->setSchedule($params['schedule'] ?? '');
            $entity->setEmails($emails);

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

            $facilities = $entity->getFacilities();
            foreach ($facilities as $facility) {
                $entity->removeFacility($facility);
            }

            if ($type->isFacility()) {
                if(!empty($params['facilities'])) {
                    /** @var FacilityRepository $facilityRepo */
                    $facilityRepo = $this->em->getRepository(Facility::class);

                    $facilityIds = array_unique($params['facilities']);
                    $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                    if (!empty($facilities)) {
                        $entity->setFacilities($facilities);
                    }
                }
            } else {
                $entity->setFacilities(null);
            }

            $apartments = $entity->getApartments();
            foreach ($apartments as $apartment) {
                $entity->removeApartment($apartment);
            }

            if ($type->isApartment()) {
                if(!empty($params['apartments'])) {
                    /** @var ApartmentRepository $apartmentRepo */
                    $apartmentRepo = $this->em->getRepository(Apartment::class);

                    $apartmentIds = array_unique($params['apartments']);
                    $apartments = $apartmentRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Apartment::class), $apartmentIds);

                    if (!empty($apartments)) {
                        $entity->setApartments($apartments);
                    }
                }
            } else {
                $entity->setApartments(null);
            }

            $regions = $entity->getRegions();
            foreach ($regions as $region) {
                $entity->removeRegion($region);
            }

            if ($type->isRegion()) {
                if(!empty($params['regions'])) {
                    /** @var RegionRepository $regionRepo */
                    $regionRepo = $this->em->getRepository(Region::class);

                    $regionIds = array_unique($params['regions']);
                    $regions = $regionRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Region::class), $regionIds);

                    if (!empty($regions)) {
                        $entity->setRegions($regions);
                    }
                }
            } else {
                $entity->setRegions(null);
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
