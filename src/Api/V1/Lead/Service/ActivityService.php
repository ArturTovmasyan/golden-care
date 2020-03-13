<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ContactNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\IncorrectActivityTypeException;
use App\Api\V1\Common\Service\Exception\Lead\IncorrectOwnerTypeException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityStatusNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\OutreachNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferralNotFoundException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ChangeLog;
use App\Entity\Facility;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\Contact;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Outreach;
use App\Entity\Lead\Referral;
use App\Entity\User;
use App\Model\ChangeLogType;
use App\Model\Lead\ActivityOwnerType;
use App\Repository\FacilityRepository;
use App\Repository\Lead\ActivityStatusRepository;
use App\Repository\Lead\ActivityRepository;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\ContactRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\OutreachRepository;
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
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
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
     * @param array $params
     * @return int|null
     * @throws \Throwable
     */
    public function add(array $params): ?int
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

            if (!\in_array($ownerType, $type->getCategories(), false)) {
                throw new IncorrectActivityTypeException();
            }

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
            } else {
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
                    $status = $statusRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $statusId);

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
                $user = $userRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userId);

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
                $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }

                $activity->setFacility($facility);
            } else {
                $activity->setFacility(null);
            }

            if ($type->isContact()) {
                $taskContactId = $params['task_contact_id'] ?? 0;

                /** @var ContactRepository $taskContactRepo */
                $taskContactRepo = $this->em->getRepository(Contact::class);

                /** @var Contact $taskContact */
                $taskContact = $taskContactRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $taskContactId);

                if ($taskContact === null) {
                    throw new ContactNotFoundException();
                }

                $activity->setTaskContact($taskContact);
            } else {
                $activity->setTaskContact(null);
            }

            if ($type->isAmount()) {
                $activity->setAmount($params['amount']);
            } else {
                $activity->setAmount(null);
            }

            switch ($ownerType) {
                case ActivityOwnerType::TYPE_LEAD:
                    $validationGroup = 'api_lead_lead_activity_add';

                    $leadId = $params['lead_id'] ?? 0;

                    /** @var LeadRepository $leadRepo */
                    $leadRepo = $this->em->getRepository(Lead::class);

                    /** @var Lead $lead */
                    $lead = $leadRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

                    if ($lead === null) {
                        throw new LeadNotFoundException();
                    }

                    $activity->setLead($lead);
                    $activity->setReferral(null);
                    $activity->setOrganization(null);
                    $activity->setOutreach(null);
                    $activity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_REFERRAL:
                    $validationGroup = 'api_lead_referral_activity_add';

                    $referralId = $params['referral_id'] ?? 0;

                    /** @var ReferralRepository $referralRepo */
                    $referralRepo = $this->em->getRepository(Referral::class);

                    /** @var Referral $referral */
                    $referral = $referralRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Referral::class), $referralId);

                    if ($referral === null) {
                        throw new ReferralNotFoundException();
                    }

                    $activity->setLead(null);
                    $activity->setReferral($referral);
                    $activity->setOrganization(null);
                    $activity->setOutreach(null);
                    $activity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_ORGANIZATION:
                    $validationGroup = 'api_lead_organization_activity_add';

                    $organizationId = $params['organization_id'] ?? 0;

                    /** @var OrganizationRepository $organizationRepo */
                    $organizationRepo = $this->em->getRepository(Organization::class);

                    /** @var Organization $organization */
                    $organization = $organizationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                    if ($organization === null) {
                        throw new OrganizationNotFoundException();
                    }

                    $activity->setLead(null);
                    $activity->setReferral(null);
                    $activity->setOrganization($organization);
                    $activity->setOutreach(null);
                    $activity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_OUTREACH:
                    $validationGroup = 'api_lead_outreach_activity_add';

                    $outreachId = $params['outreach_id'] ?? 0;

                    /** @var OutreachRepository $outreachRepo */
                    $outreachRepo = $this->em->getRepository(Outreach::class);

                    /** @var Outreach $outreach */
                    $outreach = $outreachRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Outreach::class), $outreachId);

                    if ($outreach === null) {
                        throw new OutreachNotFoundException();
                    }

                    $activity->setLead(null);
                    $activity->setReferral(null);
                    $activity->setOrganization(null);
                    $activity->setOutreach($outreach);
                    $activity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_CONTACT:
                    $validationGroup = 'api_lead_contact_activity_add';

                    $contactId = $params['contact_id'] ?? 0;

                    /** @var ContactRepository $contactRepo */
                    $contactRepo = $this->em->getRepository(Contact::class);

                    /** @var Contact $contact */
                    $contact = $contactRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $contactId);

                    if ($contact === null) {
                        throw new ContactNotFoundException();
                    }

                    $activity->setLead(null);
                    $activity->setReferral(null);
                    $activity->setOrganization(null);
                    $activity->setOutreach(null);
                    $activity->setContact($contact);

                    break;
                default:
                    throw new IncorrectOwnerTypeException();
            }

            $this->validate($activity, null, [$validationGroup]);

            $this->em->persist($activity);

            if ($activity->getType() !== null && $activity->getType()->isAssignTo()) {
                $this->taskActivityAddChangeLog($activity);
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
     * @param array $params
     * @throws \Throwable
     */
    public function edit($id, array $params): void
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
            } else {
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
                    $status = $statusRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(ActivityStatus::class), $statusId);

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
                $user = $userRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userId);

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
                $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityId);

                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }

                $entity->setFacility($facility);
            } else {
                $entity->setFacility(null);
            }

            if ($type->isContact()) {
                $taskContactId = $params['task_contact_id'] ?? 0;

                /** @var ContactRepository $taskContactRepo */
                $taskContactRepo = $this->em->getRepository(Contact::class);

                /** @var Contact $taskContact */
                $taskContact = $taskContactRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $taskContactId);

                if ($taskContact === null) {
                    throw new ContactNotFoundException();
                }

                $entity->setTaskContact($taskContact);
            } else {
                $entity->setTaskContact(null);
            }

            if ($type->isAmount()) {
                $entity->setAmount($params['amount']);
            } else {
                $entity->setAmount(null);
            }

            switch ($ownerType) {
                case ActivityOwnerType::TYPE_LEAD:
                    $validationGroup = 'api_lead_lead_activity_edit';

                    $leadId = $params['lead_id'] ?? 0;

                    /** @var LeadRepository $leadRepo */
                    $leadRepo = $this->em->getRepository(Lead::class);

                    /** @var Lead $lead */
                    $lead = $leadRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $leadId);

                    if ($lead === null) {
                        throw new LeadNotFoundException();
                    }

                    $entity->setLead($lead);
                    $entity->setReferral(null);
                    $entity->setOrganization(null);
                    $entity->setOutreach(null);
                    $entity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_REFERRAL:
                    $validationGroup = 'api_lead_referral_activity_edit';

                    $referralId = $params['referral_id'] ?? 0;

                    /** @var ReferralRepository $referralRepo */
                    $referralRepo = $this->em->getRepository(Referral::class);

                    /** @var Referral $referral */
                    $referral = $referralRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Referral::class), $referralId);

                    if ($referral === null) {
                        throw new ReferralNotFoundException();
                    }

                    $entity->setLead(null);
                    $entity->setReferral($referral);
                    $entity->setOrganization(null);
                    $entity->setOutreach(null);
                    $entity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_ORGANIZATION:
                    $validationGroup = 'api_lead_organization_activity_edit';

                    $organizationId = $params['organization_id'] ?? 0;

                    /** @var OrganizationRepository $organizationRepo */
                    $organizationRepo = $this->em->getRepository(Organization::class);

                    /** @var Organization $organization */
                    $organization = $organizationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

                    if ($organization === null) {
                        throw new OrganizationNotFoundException();
                    }

                    $entity->setLead(null);
                    $entity->setReferral(null);
                    $entity->setOrganization($organization);
                    $entity->setOutreach(null);
                    $entity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_OUTREACH:
                    $validationGroup = 'api_lead_outreach_activity_edit';

                    $outreachId = $params['outreach_id'] ?? 0;

                    /** @var OutreachRepository $outreachRepo */
                    $outreachRepo = $this->em->getRepository(Outreach::class);

                    /** @var Outreach $outreach */
                    $outreach = $outreachRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Outreach::class), $outreachId);

                    if ($outreach === null) {
                        throw new OutreachNotFoundException();
                    }

                    $entity->setLead(null);
                    $entity->setReferral(null);
                    $entity->setOrganization(null);
                    $entity->setOutreach($outreach);
                    $entity->setContact(null);

                    break;
                case ActivityOwnerType::TYPE_CONTACT:
                    $validationGroup = 'api_lead_contact_activity_edit';

                    $contactId = $params['contact_id'] ?? 0;

                    /** @var ContactRepository $contactRepo */
                    $contactRepo = $this->em->getRepository(Contact::class);

                    /** @var Contact $contact */
                    $contact = $contactRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $contactId);

                    if ($contact === null) {
                        throw new ContactNotFoundException();
                    }

                    $entity->setLead(null);
                    $entity->setReferral(null);
                    $entity->setOrganization(null);
                    $entity->setOutreach(null);
                    $entity->setContact($contact);

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
                $this->taskActivityStatusEditChangeLog($activityChangeSet['status']['0'], $activityChangeSet['status']['1'], $entity);
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
     */
    public function taskActivityAddChangeLog(Activity $activity)
    {
        $owner = '';

        switch ($activity->getOwnerType()) {
            case ActivityOwnerType::TYPE_LEAD:
                $owner = $activity->getLead() ? $activity->getLead()->getFirstName() . ' ' . $activity->getLead()->getLastName() : '';
                $id = $activity->getLead()->getId();

                break;
            case ActivityOwnerType::TYPE_REFERRAL:
                if ($activity->getReferral() !== null) {
                    if ($activity->getReferral()->getContact() !== null) {
                        $owner = $activity->getReferral()->getContact()->getFirstName() . ' ' . $activity->getReferral()->getContact()->getLastName();
                    } else {
                        $owner = $activity->getReferral()->getOrganization() ? $activity->getReferral()->getOrganization()->getName() : '';
                    }
                }
                $id = $activity->getReferral()->getId();

                break;
            case ActivityOwnerType::TYPE_ORGANIZATION:
                $owner = $activity->getOrganization() ? $activity->getOrganization()->getName() : '';
                $id = $activity->getOrganization()->getId();

                break;
            case ActivityOwnerType::TYPE_OUTREACH:
                if ($activity->getOutreach() !== null && $activity->getOutreach()->getType() !== null) {
                    $owner = $activity->getOutreach()->getType()->getTitle();
                }
                $id = $activity->getOutreach()->getId();

                break;
            case ActivityOwnerType::TYPE_CONTACT:
                $owner = $activity->getContact() ? $activity->getContact()->getFirstName() . ' ' . $activity->getContact()->getLastName() : '';
                $id = $activity->getContact()->getId();

                break;
            default:
                throw new IncorrectOwnerTypeException();
        }

        $userName = $activity->getUpdatedBy() !== null ? ucfirst($activity->getUpdatedBy()->getFullName()) : '';
        $assignToName = $activity->getAssignTo() ? $activity->getAssignTo()->getFirstName() . ' ' . $activity->getAssignTo()->getLastName() : '';
        $dueDate = $activity->getDueDate() !== null ? $activity->getDueDate()->format('m/d/Y') : 'N/A';
        $date = new \DateTime('now');

        $content = [
            'type' => $activity->getOwnerType(),
            'owner' => $owner,
            'id' => $id,
            'assign_to' => $assignToName,
            'due_date' => $dueDate,
            'name' => $activity->getTitle(),
            'user_name' => $userName,
            'created_at' => $date->format('m/d/Y H:i')
        ];

        $changeLog = new ChangeLog();
        $changeLog->setType(ChangeLogType::TYPE_NEW_TASK);
        $changeLog->setContent($content);
        $changeLog->setOwner($activity->getAssignTo());

        $space = $activity->getStatus() ? $activity->getStatus()->getSpace() : null;
        $changeLog->setSpace($space);

        $this->validate($changeLog, null, ['api_admin_change_log_add']);

        $this->em->persist($changeLog);
    }

    /**
     * @param $oldStatusId
     * @param $newStatusId
     * @param Activity $activity
     */
    private function taskActivityStatusEditChangeLog($oldStatusId, $newStatusId, Activity $activity)
    {
        $owner = '';

        switch ($activity->getOwnerType()) {
            case ActivityOwnerType::TYPE_LEAD:
                $owner = $activity->getLead() ? $activity->getLead()->getFirstName() . ' ' . $activity->getLead()->getLastName() : '';
                $id = $activity->getLead()->getId();

                break;
            case ActivityOwnerType::TYPE_REFERRAL:
                if ($activity->getReferral() !== null) {
                    if ($activity->getReferral()->getContact() !== null) {
                        $owner = $activity->getReferral()->getContact()->getFirstName() . ' ' . $activity->getReferral()->getContact()->getLastName();
                    } else {
                        $owner = $activity->getReferral()->getOrganization() ? $activity->getReferral()->getOrganization()->getName() : '';
                    }
                }
                $id = $activity->getReferral()->getId();

                break;
            case ActivityOwnerType::TYPE_ORGANIZATION:
                $owner = $activity->getOrganization() ? $activity->getOrganization()->getName() : '';
                $id = $activity->getOrganization()->getId();

                break;
            case ActivityOwnerType::TYPE_OUTREACH:
                if ($activity->getOutreach() !== null && $activity->getOutreach()->getType() !== null) {
                    $owner = $activity->getOutreach()->getType()->getTitle();
                }
                $id = $activity->getOutreach()->getId();

                break;
            case ActivityOwnerType::TYPE_CONTACT:
                $owner = $activity->getContact() ? $activity->getContact()->getFirstName() . ' ' . $activity->getContact()->getLastName() : '';
                $id = $activity->getContact()->getId();

                break;
            default:
                throw new IncorrectOwnerTypeException();
        }

        $userName = $activity->getUpdatedBy() !== null ? ucfirst($activity->getUpdatedBy()->getFullName()) : '';
        $assignToName = $activity->getAssignTo() ? $activity->getAssignTo()->getFirstName() . ' ' . $activity->getAssignTo()->getLastName() : '';
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

        $date = new \DateTime('now');

        $content = [
            'type' => $activity->getOwnerType(),
            'owner' => $owner,
            'name' => $activity->getTitle(),
            'id' => $id,
            'assign_to' => $assignToName,
            'due_date' => $dueDate,
            'user_name' => $userName,
            'old_status' => $oldStatus->getTitle(),
            'new_status' => $newStatus->getTitle(),
            'created_at' => $date->format('m/d/Y H:i')
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
