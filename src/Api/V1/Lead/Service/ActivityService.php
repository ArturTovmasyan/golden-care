<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\IncorrectOwnerTypeException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityStatusNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferralNotFoundException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Facility;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Referral;
use App\Entity\User;
use App\Model\Lead\ActivityOwnerType;
use App\Repository\FacilityRepository;
use App\Repository\Lead\ActivityStatusRepository;
use App\Repository\Lead\ActivityRepository;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ReferralRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ActivityService
 * @package App\Api\V1\Admin\Service
 */
class ActivityService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Activity::class), $queryBuilder);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Activity::class));
    }

    /**
     * @param $id
     * @return Activity|null|object
     */
    public function getById($id)
    {
        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Activity::class), $id);
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

            /** @var ActivityTypeRepository $typeRepo */
            $typeRepo = $this->em->getRepository(ActivityType::class);

            /** @var ActivityType $type */
            $type = $typeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $typeId);

            if ($type === null) {
                throw new ActivityTypeNotFoundException();
            }

            $ownerType = $params['owner_type'] ? (int)$params['owner_type'] : 0;
            $notes = $params['notes'] ?? '';

            $activity = new Activity();
            $activity->setType($type);
            $activity->setOwnerType($ownerType);
            $activity->setNotes($notes);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $activity->setDate($date);

            if (!empty($params['title'])) {
                $activity->setTitle($params['title']);
            } else{
                $activity->setTitle($type->getTitle());
            }

            if ($type->getDefaultStatus()) {
                if ($type->getDefaultStatus()->isDone() === true) {
                    $activity->setStatus($type->getDefaultStatus());
                } else {
                    $statusId = $params['status_id'] ?? 0;

                    /** @var ActivityStatusRepository $statusRepo */
                    $statusRepo = $this->em->getRepository(ActivityStatus::class);

                    /** @var ActivityStatus $status */
                    $status = $statusRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $statusId);

                    if ($status === null) {
                        throw new ActivityStatusNotFoundException();
                    }

                    $activity->setStatus($status);
                }
            }

            if ($type->isAssignTo()) {
                $userId = $params['assign_to'] ?? 0;

                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                /** @var User $user */
                $user = $userRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(User::class), $userId);

                if ($user === null) {
                    throw new UserNotFoundException();
                }

                $activity->setAssignTo($user);
            } else {
                $activity->setAssignTo(null);
            }

            if ($type->isDueDate()) {
                $dueDate = $params['due_date'];

                if (!empty($dueDate)) {
                    $dueDate = new \DateTime($params['due_date']);
                } else {
                    $dueDate = null;
                }

                $activity->setDueDate($dueDate);
            } else {
                $activity->setDueDate(null);
            }

            if ($type->isReminderDate()) {
                $reminderDate = $params['reminder_date'];

                if (!empty($reminderDate)) {
                    $reminderDate = new \DateTime($params['reminder_date']);
                } else {
                    $reminderDate = null;
                }

                $activity->setReminderDate($reminderDate);
            } else {
                $activity->setReminderDate(null);
            }

            if ($type->isFacility()) {
                $facilityId = $params['facility_id'] ?? 0;

                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                /** @var Facility $facility */
                $facility = $facilityRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }

                $activity->setFacility($facility);
            } else {
                $activity->setFacility(null);
            }

            switch ($ownerType) {
                case ActivityOwnerType::TYPE_LEAD:
                    $validationGroup = 'api_lead_lead_activity_add';

                    $activity->setReferral(null);
                    $activity->setOrganization(null);

                    break;
                case ActivityOwnerType::TYPE_REFERRAL:
                    $validationGroup = 'api_lead_referral_activity_add';

                    $referralId = $params['referral_id'] ?? 0;

                    /** @var ReferralRepository $referralRepo */
                    $referralRepo = $this->em->getRepository(Referral::class);

                    /** @var Referral $referral */
                    $referral = $referralRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Referral::class), $referralId);

                    if ($referral === null) {
                        throw new ReferralNotFoundException();
                    }

                    $activity->setReferral($referral);
                    $activity->setOrganization(null);

                    break;
                case ActivityOwnerType::TYPE_ORGANIZATION:
                    $validationGroup = 'api_lead_organization_activity_add';

                    $organizationId = $params['organization_id'] ?? 0;

                    /** @var OrganizationRepository $organizationRepo */
                    $organizationRepo = $this->em->getRepository(Organization::class);

                    /** @var Organization $organization */
                    $organization = $organizationRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                    if ($organization === null) {
                        throw new OrganizationNotFoundException();
                    }

                    $activity->setReferral(null);
                    $activity->setOrganization($organization);

                    break;
                default:
                    throw new IncorrectOwnerTypeException();
            }

            $this->validate($activity, null, [$validationGroup]);

            $this->em->persist($activity);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $activity->getId();
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

            /** @var ActivityRepository $repo */
            $repo = $this->em->getRepository(Activity::class);

            /** @var Activity $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Activity::class), $id);

            if ($entity === null) {
                throw new ActivityNotFoundException();
            }

            /** @var ActivityType $type */
            $type = $entity->getType();
            $ownerType = $entity->getOwnerType();

            $notes = $params['notes'] ?? '';

            $entity->setNotes($notes);

            $date = $params['date'];

            if (!empty($date)) {
                $date = new \DateTime($params['date']);
            }

            $entity->setDate($date);

            if (!empty($params['title'])) {
                $entity->setTitle($params['title']);
            } else{
                $entity->setTitle($type->getTitle());
            }

            if ($type->getDefaultStatus()) {
                if ($type->getDefaultStatus()->isDone() === true) {
                    $entity->setStatus($type->getDefaultStatus());
                } else {
                    $statusId = $params['status_id'] ?? 0;

                    /** @var ActivityStatusRepository $statusRepo */
                    $statusRepo = $this->em->getRepository(ActivityStatus::class);

                    /** @var ActivityStatus $status */
                    $status = $statusRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $statusId);

                    if ($status === null) {
                        throw new ActivityStatusNotFoundException();
                    }

                    $entity->setStatus($status);
                }
            }

            if ($type->isAssignTo()) {
                $userId = $params['assign_to'] ?? 0;

                /** @var UserRepository $userRepo */
                $userRepo = $this->em->getRepository(User::class);

                /** @var User $user */
                $user = $userRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(User::class), $userId);

                if ($user === null) {
                    throw new UserNotFoundException();
                }

                $entity->setAssignTo($user);
            } else {
                $entity->setAssignTo(null);
            }

            if ($type->isDueDate()) {
                $dueDate = $params['due_date'];

                if (!empty($dueDate)) {
                    $dueDate = new \DateTime($params['due_date']);
                } else {
                    $dueDate = null;
                }

                $entity->setDueDate($dueDate);
            } else {
                $entity->setDueDate(null);
            }

            if ($type->isReminderDate()) {
                $reminderDate = $params['reminder_date'];

                if (!empty($reminderDate)) {
                    $reminderDate = new \DateTime($params['reminder_date']);
                } else {
                    $reminderDate = null;
                }

                $entity->setReminderDate($reminderDate);
            } else {
                $entity->setReminderDate(null);
            }

            if ($type->isFacility()) {
                $facilityId = $params['facility_id'] ?? 0;

                /** @var FacilityRepository $facilityRepo */
                $facilityRepo = $this->em->getRepository(Facility::class);

                /** @var Facility $facility */
                $facility = $facilityRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }

                $entity->setFacility($facility);
            } else {
                $entity->setFacility(null);
            }

            switch ($ownerType) {
                case ActivityOwnerType::TYPE_LEAD:
                    $validationGroup = 'api_lead_lead_activity_edit';

                    $entity->setReferral(null);
                    $entity->setOrganization(null);

                    break;
                case ActivityOwnerType::TYPE_REFERRAL:
                    $validationGroup = 'api_lead_referral_activity_edit';

                    $referralId = $params['referral_id'] ?? 0;

                    /** @var ReferralRepository $referralRepo */
                    $referralRepo = $this->em->getRepository(Referral::class);

                    /** @var Referral $referral */
                    $referral = $referralRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Referral::class), $referralId);

                    if ($referral === null) {
                        throw new ReferralNotFoundException();
                    }

                    $entity->setReferral($referral);
                    $entity->setOrganization(null);

                    break;
                case ActivityOwnerType::TYPE_ORGANIZATION:
                    $validationGroup = 'api_lead_organization_activity_edit';

                    $organizationId = $params['organization_id'] ?? 0;

                    /** @var OrganizationRepository $organizationRepo */
                    $organizationRepo = $this->em->getRepository(Organization::class);

                    /** @var Organization $organization */
                    $organization = $organizationRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                    if ($organization === null) {
                        throw new OrganizationNotFoundException();
                    }

                    $entity->setReferral(null);
                    $entity->setOrganization($organization);

                    break;
                default:
                    throw new IncorrectOwnerTypeException();
            }

            $this->validate($entity, null, [$validationGroup]);

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

            /** @var ActivityRepository $repo */
            $repo = $this->em->getRepository(Activity::class);

            /** @var Activity $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Activity::class), $id);

            if ($entity === null) {
                throw new ActivityNotFoundException();
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
                throw new ActivityNotFoundException();
            }

            /** @var ActivityRepository $repo */
            $repo = $this->em->getRepository(Activity::class);

            $activities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Activity::class), $ids);

            if (empty($activities)) {
                throw new ActivityNotFoundException();
            }

            /**
             * @var Activity $activity
             */
            foreach ($activities as $activity) {
                $this->em->remove($activity);
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
            throw new ActivityNotFoundException();
        }

        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Activity::class), $ids);

        if (empty($entities)) {
            throw new ActivityNotFoundException();
        }

        return $this->getRelatedData(Activity::class, $entities);
    }
}