<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\IncorrectOwnerTypeException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityStatusNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferralNotFoundException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ChangeLog;
use App\Entity\Facility;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Referral;
use App\Entity\User;
use App\Model\ChangeLogType;
use App\Model\Lead\ActivityOwnerType;
use App\Repository\FacilityRepository;
use App\Repository\Lead\ActivityStatusRepository;
use App\Repository\Lead\ActivityRepository;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ReferralRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\RouterInterface;

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

        $ownerType = null;
        $ownerId = null;
        $userId = null;
        if (!empty($params)) {
            if (!empty($params[0]['owner_type']) && !empty($params[0]['owner_id'])) {
                $ownerType = $params[0]['owner_type'];
                $ownerId = $params[0]['owner_id'];
                $userId = null;
            }

            if (isset($params[0]['my']) && !empty($params[0]['user_id'])) {
                $userId = $params[0]['user_id'];
                $ownerType = null;
                $ownerId = null;
            }
        }


        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Activity::class), $queryBuilder, $ownerType, $ownerId, $userId);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();
        $entityGrants = $this->grantService->getCurrentUserEntityGrants(Activity::class);

        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        if (!empty($params)) {
            if (isset($params[0]['my']) && !empty($params[0]['user_id'])) {
                $userId = $params[0]['user_id'];

                return $repo->getMy($currentSpace, $entityGrants, $userId);
            }

            if (!empty($params[0]['owner_type']) && !empty($params[0]['owner_id'])) {
                $ownerType = $params[0]['owner_type'];
                $ownerId = $params[0]['owner_id'];

                return $repo->getBy($currentSpace, $entityGrants, $ownerType, $ownerId);
            }
        }

        return $repo->list($currentSpace, $entityGrants);
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
     * @param RouterInterface $router
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(RouterInterface $router, array $params) : ?int
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
                $userId = $params['assign_to_id'] ?? 0;

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

                    $leadId = $params['lead_id'] ?? 0;

                    /** @var LeadRepository $leadRepo */
                    $leadRepo = $this->em->getRepository(Lead::class);

                    /** @var Lead $lead */
                    $lead = $leadRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

                    if ($lead === null) {
                        throw new LeadNotFoundException();
                    }

                    $activity->setLead($lead);
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

                    $activity->setLead(null);
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

                    $activity->setLead(null);
                    $activity->setReferral(null);
                    $activity->setOrganization($organization);

                    break;
                default:
                    throw new IncorrectOwnerTypeException();
            }

            $this->validate($activity, null, [$validationGroup]);

            $this->em->persist($activity);

            if ($activity->getType() !== null && $activity->getType()->isAssignTo()) {
                $this->taskActivityAddChangeLog($activity, $router);
            }

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
     * @param RouterInterface $router
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, RouterInterface $router, array $params) : void
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
                $userId = $params['assign_to_id'] ?? 0;

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

                    $leadId = $params['lead_id'] ?? 0;

                    /** @var LeadRepository $leadRepo */
                    $leadRepo = $this->em->getRepository(Lead::class);

                    /** @var Lead $lead */
                    $lead = $leadRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

                    if ($lead === null) {
                        throw new LeadNotFoundException();
                    }

                    $entity->setLead($lead);
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

                    $entity->setLead(null);
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

                    $entity->setLead(null);
                    $entity->setReferral(null);
                    $entity->setOrganization($organization);

                    break;
                default:
                    throw new IncorrectOwnerTypeException();
            }

            $this->validate($entity, null, [$validationGroup]);

            $this->em->persist($entity);

            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();

            $activityChangeSet = $this->em->getUnitOfWork()->getEntityChangeSet($entity);

            if (!empty($activityChangeSet) && array_key_exists('status', $activityChangeSet) && $entity->getType() !== null && $entity->getType()->isAssignTo()) {
                $this->taskActivityStatusEditChangeLog($activityChangeSet['status']['0'], $activityChangeSet['status']['1'], $entity, $router);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Activity $activity
     * @param RouterInterface $router
     */
    private function taskActivityAddChangeLog(Activity $activity, RouterInterface $router)
    {
        $ownerTitle = 'New Activity Task';
        $owner = '';

        switch ($activity->getOwnerType()) {
            case ActivityOwnerType::TYPE_LEAD:
                $ownerName = ActivityOwnerType::getTypes()[ActivityOwnerType::TYPE_LEAD];
                $owner =  $activity->getLead() ? $activity->getLead()->getFirstName() . ' ' . $activity->getLead()->getLastName() : '';
                $id = $activity->getLead()->getId();

                break;
            case ActivityOwnerType::TYPE_REFERRAL:
                $ownerName = ActivityOwnerType::getTypes()[ActivityOwnerType::TYPE_REFERRAL];;
                if ($activity->getReferral() !== null) {
                    if ($activity->getReferral()->getFirstName() === null) {
                        $owner = $activity->getReferral()->getFirstName() . ' ' . $activity->getReferral()->getLastName();
                    } else {
                        $owner = $activity->getReferral()->getOrganization() ? $activity->getReferral()->getOrganization()->getTitle() : '';
                    }
                }
                $id = $activity->getReferral()->getId();

                break;
            case ActivityOwnerType::TYPE_ORGANIZATION:
                $ownerName = ActivityOwnerType::getTypes()[ActivityOwnerType::TYPE_ORGANIZATION];;
                $owner =  $activity->getOrganization() ? $activity->getOrganization()->getTitle() : '';
                $id = $activity->getOrganization()->getId();

                break;
            default:
                throw new IncorrectOwnerTypeException();
        }

        $userName = $activity->getUpdatedBy() ? ucfirst($activity->getUpdatedBy()->getFullName()) : '';
        $assignToName =  $activity->getAssignTo() ? $activity->getAssignTo()->getFirstName() . ' ' . $activity->getAssignTo()->getLastName() : '';
        $dueDate = $activity->getDueDate() !== null ? $activity->getDueDate()->format('m/d/Y') : 'N/A';

        $content = [
            'type' => $activity->getOwnerType(),
            'owner' => $owner,
            'id' => $id,
            'assign_to' => $assignToName,
            'due_date' => $dueDate,
            'name' => $activity->getTitle(),
            'user_name' => $userName
        ];

        $changeLog = new ChangeLog();
        $changeLog->setType(ChangeLogType::TYPE_NEW_TASK);
        $changeLog->setContent($content);
        $changeLog->setOwner($activity->getAssignTo() ?? null);

        $space = $activity->getStatus() ? $activity->getStatus()->getSpace() : null;
        $changeLog->setSpace($space);

        $this->validate($changeLog, null, ['api_admin_change_log_add']);

        $this->em->persist($changeLog);
    }

    /**
     * @param $oldStatusId
     * @param $newStatusId
     * @param Activity $activity
     * @param RouterInterface $router
     */
    private function taskActivityStatusEditChangeLog($oldStatusId, $newStatusId, Activity $activity, RouterInterface $router)
    {
        $ownerTitle = 'Modified Activity Task Status';
        $owner = '';

        switch ($activity->getOwnerType()) {
            case ActivityOwnerType::TYPE_LEAD:
                $ownerName = ActivityOwnerType::getTypes()[ActivityOwnerType::TYPE_LEAD];
                $owner =  $activity->getLead() ? $activity->getLead()->getFirstName() . ' ' . $activity->getLead()->getLastName() : '';
                $id = $activity->getLead()->getId();

                break;
            case ActivityOwnerType::TYPE_REFERRAL:
                $ownerName = ActivityOwnerType::getTypes()[ActivityOwnerType::TYPE_REFERRAL];;
                if ($activity->getReferral() !== null) {
                    if ($activity->getReferral()->getFirstName() === null) {
                        $owner = $activity->getReferral()->getFirstName() . ' ' . $activity->getReferral()->getLastName();
                    } else {
                        $owner = $activity->getReferral()->getOrganization() ? $activity->getReferral()->getOrganization()->getTitle() : '';
                    }
                }
                $id = $activity->getReferral()->getId();

                break;
            case ActivityOwnerType::TYPE_ORGANIZATION:
                $ownerName = ActivityOwnerType::getTypes()[ActivityOwnerType::TYPE_ORGANIZATION];;
                $owner =  $activity->getOrganization() ? $activity->getOrganization()->getTitle() : '';
                $id = $activity->getOrganization()->getId();

                break;
            default:
                throw new IncorrectOwnerTypeException();
        }

        $userName = $activity->getUpdatedBy() ? ucfirst($activity->getUpdatedBy()->getFullName()) : '';
        $assignToName =  $activity->getAssignTo() ? $activity->getAssignTo()->getFirstName() . ' ' . $activity->getAssignTo()->getLastName() : '';
        $dueDate = $activity->getDueDate() !== null ? $activity->getDueDate()->format('m/d/Y') : 'N/A';

        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var ActivityStatusRepository $statusRepo */
        $statusRepo = $this->em->getRepository(ActivityStatus::class);

        /** @var ActivityStatus $oldStatus */
        $oldStatus = $statusRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $oldStatusId);

        if ($oldStatus === null) {
            throw new ActivityStatusNotFoundException();
        }

        /** @var ActivityStatus $newStatus */
        $newStatus = $statusRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $newStatusId);

        if ($newStatus === null) {
            throw new ActivityStatusNotFoundException();
        }

        $content = [
            'type' => $activity->getOwnerType(),
            'owner' => $owner,
            'id' => $id,
            'assign_to' => $assignToName,
            'due_date' => $dueDate,
            'old_status' => $oldStatus->getTitle(),
            'new_status' => $newStatus->getTitle()
        ];

        $changeLog = new ChangeLog();
        $changeLog->setType(ChangeLogType::TYPE_TASK_UPDATED);
        $changeLog->setContent($content);
        $changeLog->setOwner($activity->getAssignTo() ?? null);

        $space = $activity->getStatus() ? $activity->getStatus()->getSpace() : null;
        $changeLog->setSpace($space);

        $this->validate($changeLog, null, ['api_admin_change_log_edit']);

        $this->em->persist($changeLog);
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
