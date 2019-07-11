<?php
namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\CareTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadRpPhoneOrEmailNotBeBlankException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferrerTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\StateChangeReasonNotFoundException;
use App\Api\V1\Common\Service\Exception\PaymentSourceNotFoundException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ChangeLog;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\CareType;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferrerType;
use App\Entity\Lead\StateChangeReason;
use App\Entity\PaymentSource;
use App\Entity\User;
use App\Model\ChangeLogType;
use App\Model\Lead\ActivityOwnerType;
use App\Model\Lead\State;
use App\Repository\CityStateZipRepository;
use App\Repository\FacilityRepository;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\CareTypeRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ReferrerTypeRepository;
use App\Repository\Lead\StateChangeReasonRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LeadService
 * @package App\Api\V1\Admin\Service
 */
class LeadService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params) : void
    {
        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        $all = false;
        if (!empty($params) && isset($params[0]['all'])) {
            $all = true;
        }

        $repo->search($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $queryBuilder, $all);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        $free = false;
        if (!empty($params) && isset($params[0]['free'])) {
            $free = true;
        }

        $all = false;
        if (!empty($params) && isset($params[0]['all'])) {
            $all = true;
        }

        return $repo->list($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $all, $free);
    }

    /**
     * @param $id
     * @return Lead|null|object
     */
    public function getById($id)
    {
        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        return $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $id);
    }

    /**
     * @param ReferralService $referralService
     * @param RouterInterface $router
     * @param array $params
     * @return int|null
     * @throws \Exception
     */
    public function add(ReferralService $referralService, RouterInterface $router, array $params) : ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            $lead = new Lead();
            $lead->setFirstName($params['first_name']);
            $lead->setLastName($params['last_name']);

            if (!empty($params['care_type_id'])) {
                /** @var CareTypeRepository $careTypeRepo */
                $careTypeRepo = $this->em->getRepository(CareType::class);

                /** @var CareType $careType */
                $careType = $careTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareType::class), $params['care_type_id']);

                if ($careType === null) {
                    throw new CareTypeNotFoundException();
                }

                $lead->setCareType($careType);
            } else {
                $lead->setCareType(null);
            }

            if (!empty($params['payment_type_id'])) {
                /** @var PaymentSourceRepository $paymentTypeRepo */
                $paymentTypeRepo = $this->em->getRepository(PaymentSource::class);

                /** @var PaymentSource $paymentType */
                $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $params['payment_type_id']);

                if ($paymentType === null) {
                    throw new PaymentSourceNotFoundException();
                }

                $lead->setPaymentType($paymentType);
            } else {
                $lead->setPaymentType(null);
            }

            $ownerId = $params['owner_id'] ?? 0;

            /** @var UserRepository $ownerRepo */
            $ownerRepo = $this->em->getRepository(User::class);

            /** @var User $owner */
            $owner = $ownerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $ownerId);

            if ($owner === null) {
                throw new UserNotFoundException();
            }

            $lead->setOwner($owner);
            $lead->setState(State::TYPE_OPEN);

            if (!empty($params['initial_contact_date'])) {
                $initialContactDate = new \DateTime($params['initial_contact_date']);
            } else {
                $initialContactDate = null;
            }

            $lead->setInitialContactDate($initialContactDate);

            if (!empty($params['state_change_reason_id'])) {
                /** @var StateChangeReasonRepository $stateChangeReasonRepo */
                $stateChangeReasonRepo = $this->em->getRepository(StateChangeReason::class);

                /** @var StateChangeReason $stateChangeReason */
                $stateChangeReason = $stateChangeReasonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $params['state_change_reason_id']);

                if ($stateChangeReason === null) {
                    throw new StateChangeReasonNotFoundException();
                }

                if ($stateChangeReason->getState() === State::TYPE_OPEN) {
                    $lead->setStateChangeReason($stateChangeReason);
                } else {
                    $lead->setStateChangeReason(null);
                }
            } else {
                $lead->setStateChangeReason(null);
            }

            if (!empty($params['state_effective_date'])) {
                $stateEffectiveDate = new \DateTime($params['state_effective_date']);
            } else {
                $stateEffectiveDate = new \DateTime('now');
            }

            $lead->setStateEffectiveDate($stateEffectiveDate);

            $lead->setResponsiblePersonFirstName($params['responsible_person_first_name']);
            $lead->setResponsiblePersonLastName($params['responsible_person_last_name']);

            if (!empty($params['responsible_person_address_1'])) {
                $lead->setResponsiblePersonAddress1($params['responsible_person_address_1']);
            } else {
                $lead->setResponsiblePersonAddress1(null);
            }

            if (!empty($params['responsible_person_address_2'])) {
                $lead->setResponsiblePersonAddress2($params['responsible_person_address_2']);
            } else {
                $lead->setResponsiblePersonAddress2(null);
            }

            if (!empty($params['responsible_person_csz_id'])) {
                /** @var CityStateZipRepository $cszRepo */
                $cszRepo = $this->em->getRepository(CityStateZip::class);

                /** @var CityStateZip $csz */
                $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['responsible_person_csz_id']);

                if ($csz === null) {
                    throw new CityStateZipNotFoundException();
                }

                $lead->setResponsiblePersonCsz($csz);
            } else {
                $lead->setResponsiblePersonCsz(null);
            }

            if (!empty($params['responsible_person_phone'])) {
                $lead->setResponsiblePersonPhone($params['responsible_person_phone']);
            } else {
                $lead->setResponsiblePersonPhone(null);
            }

            if (!empty($params['responsible_person_email'])) {
                $lead->setResponsiblePersonEmail($params['responsible_person_email']);
            } else {
                $lead->setResponsiblePersonEmail(null);
            }

            if ($lead->getResponsiblePersonPhone() === null && $lead->getResponsiblePersonEmail() === null) {
                throw new LeadRpPhoneOrEmailNotBeBlankException();
            }

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            if (!empty($params['primary_facility_id'])) {
                /** @var Facility $facility */
                $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['primary_facility_id']);

                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }

                $lead->setPrimaryFacility($facility);
            } else {
                $lead->setPrimaryFacility(null);
            }

            if(!empty($params['facilities'])) {
                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                if (!empty($facilities)) {
                    $lead->setFacilities($facilities);
                }
            }

            $notes = $params['notes'] ?? '';

            $lead->setNotes($notes);

            $this->validate($lead, null, ['api_lead_lead_add']);

            $this->em->persist($lead);

            // Save Referral
            if (!empty($params['referral'])) {
                $newReferral = $params['referral'];

                $this->saveReferral($lead, $referralService, $newReferral);
            }

            // Creating initial contact activity
            $this->createLeadInitialContactActivity($lead, false);

            $this->em->flush();

            // Creating change log
            $this->leadAddChangeLog($lead, $router);

            $this->em->flush();
            $this->em->getConnection()->commit();

            $insert_id = $lead->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param $id
     * @param ReferralService $referralService
     * @param RouterInterface $router
     * @param array $params
     * @throws \Exception
     */
    public function edit($id, ReferralService $referralService, RouterInterface $router, array $params) : void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            $currentSpace = $this->grantService->getCurrentSpace();

            /** @var LeadRepository $repo */
            $repo = $this->em->getRepository(Lead::class);

            /** @var Lead $entity */
            $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $id);

            if ($entity === null) {
                throw new LeadNotFoundException();
            }

            $entity->setFirstName($params['first_name']);
            $entity->setLastName($params['last_name']);

            if (!empty($params['care_type_id'])) {
                /** @var CareTypeRepository $careTypeRepo */
                $careTypeRepo = $this->em->getRepository(CareType::class);

                /** @var CareType $careType */
                $careType = $careTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareType::class), $params['care_type_id']);

                if ($careType === null) {
                    throw new CareTypeNotFoundException();
                }

                $entity->setCareType($careType);
            } else {
                $entity->setCareType(null);
            }

            if (!empty($params['payment_type_id'])) {
                /** @var PaymentSourceRepository $paymentTypeRepo */
                $paymentTypeRepo = $this->em->getRepository(PaymentSource::class);

                /** @var PaymentSource $paymentType */
                $paymentType = $paymentTypeRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(PaymentSource::class), $params['payment_type_id']);

                if ($paymentType === null) {
                    throw new PaymentSourceNotFoundException();
                }

                $entity->setPaymentType($paymentType);
            } else {
                $entity->setPaymentType(null);
            }

            $ownerId = $params['owner_id'] ?? 0;

            /** @var UserRepository $ownerRepo */
            $ownerRepo = $this->em->getRepository(User::class);

            /** @var User $owner */
            $owner = $ownerRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $ownerId);

            if ($owner === null) {
                throw new UserNotFoundException();
            }

            $entity->setOwner($owner);

            if (!empty($params['state_change_reason_id'])) {
                /** @var StateChangeReasonRepository $stateChangeReasonRepo */
                $stateChangeReasonRepo = $this->em->getRepository(StateChangeReason::class);

                /** @var StateChangeReason $stateChangeReason */
                $stateChangeReason = $stateChangeReasonRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(StateChangeReason::class), $params['state_change_reason_id']);

                if ($stateChangeReason === null) {
                    throw new StateChangeReasonNotFoundException();
                }

                $entity->setStateChangeReason($stateChangeReason);
                $entity->setState($stateChangeReason->getState());
            } else {
                $entity->setStateChangeReason(null);
                $entity->setState(State::TYPE_OPEN);
            }

            if (!empty($params['state_effective_date'])) {
                $stateEffectiveDate = new \DateTime($params['state_effective_date']);
            } else {
                $stateEffectiveDate = new \DateTime('now');
            }

            $entity->setStateEffectiveDate($stateEffectiveDate);

            $entity->setResponsiblePersonFirstName($params['responsible_person_first_name']);
            $entity->setResponsiblePersonLastName($params['responsible_person_last_name']);

            if (!empty($params['responsible_person_address_1'])) {
                $entity->setResponsiblePersonAddress1($params['responsible_person_address_1']);
            } else {
                $entity->setResponsiblePersonAddress1(null);
            }

            if (!empty($params['responsible_person_address_2'])) {
                $entity->setResponsiblePersonAddress2($params['responsible_person_address_2']);
            } else {
                $entity->setResponsiblePersonAddress2(null);
            }

            if (!empty($params['responsible_person_csz_id'])) {
                /** @var CityStateZipRepository $cszRepo */
                $cszRepo = $this->em->getRepository(CityStateZip::class);

                /** @var CityStateZip $csz */
                $csz = $cszRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CityStateZip::class), $params['responsible_person_csz_id']);

                if ($csz === null) {
                    throw new CityStateZipNotFoundException();
                }

                $entity->setResponsiblePersonCsz($csz);
            } else {
                $entity->setResponsiblePersonCsz(null);
            }

            if (!empty($params['responsible_person_phone'])) {
                $entity->setResponsiblePersonPhone($params['responsible_person_phone']);
            } else {
                $entity->setResponsiblePersonPhone(null);
            }

            if (!empty($params['responsible_person_email'])) {
                $entity->setResponsiblePersonEmail($params['responsible_person_email']);
            } else {
                $entity->setResponsiblePersonEmail(null);
            }

            if ($entity->getResponsiblePersonPhone() === null && $entity->getResponsiblePersonEmail() === null) {
                throw new LeadRpPhoneOrEmailNotBeBlankException();
            }

            /** @var FacilityRepository $facilityRepo */
            $facilityRepo = $this->em->getRepository(Facility::class);

            if (!empty($params['primary_facility_id'])) {
                /** @var Facility $facility */
                $facility = $facilityRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $params['primary_facility_id']);

                if ($facility === null) {
                    throw new FacilityNotFoundException();
                }

                $entity->setPrimaryFacility($facility);
            } else {
                $entity->setPrimaryFacility(null);
            }

            $facilities = $entity->getFacilities();
            foreach ($facilities as $facility) {
                $entity->removeFacility($facility);
            }

            if(!empty($params['facilities'])) {
                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class), $facilityIds);

                if (!empty($facilities)) {
                    $entity->setFacilities($facilities);
                }
            }

            $notes = $params['notes'] ?? '';

            $entity->setNotes($notes);

            $this->validate($entity, null, ['api_lead_lead_edit']);

            $this->em->persist($entity);

            // Save Referral
            if (!empty($params['referral'])) {
                $newReferral = $params['referral'];

                $this->saveReferral($entity, $referralService, $newReferral);
            }

            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();

            $leadChangeSet = $this->em->getUnitOfWork()->getEntityChangeSet($entity);

            if (!empty($leadChangeSet) && array_key_exists('state', $leadChangeSet)) {
                if ($leadChangeSet['state']['0'] === State::TYPE_CLOSED && $leadChangeSet['state']['1'] === State::TYPE_OPEN) {
                    $this->createLeadInitialContactActivity($entity, true);
                } elseif ($leadChangeSet['state']['0'] === State::TYPE_OPEN && $leadChangeSet['state']['1'] === State::TYPE_CLOSED) {
                    $this->createLeadStateChangeReasonActivity($entity);
                }

                $this->leadStateEditChangeLog($leadChangeSet['state']['0'], $leadChangeSet['state']['1'], $entity, $router);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param Lead $lead
     * @param ReferralService $referralService
     * @param array $newReferral
     */
    private function saveReferral(Lead $lead, ReferralService $referralService, array $newReferral)
    {
        $oldReferral = $lead->getReferral();

        if ($oldReferral) {
            $organizationRequiredValidationGroup = 'api_lead_referral_organization_required_add';
            $representativeRequiredValidationGroup = 'api_lead_referral_representative_required_add';

            $referral = $oldReferral;
        } else {
            $organizationRequiredValidationGroup = 'api_lead_referral_organization_required_edit';
            $representativeRequiredValidationGroup = 'api_lead_referral_representative_required_edit';

            $referral = new Referral();
        }

        $typeId = $newReferral['type_id'] ?? 0;

        /** @var ReferrerTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ReferrerType::class);

        /** @var ReferrerType $type */
        $type = $typeRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $typeId);

        if ($type === null) {
            throw new ReferrerTypeNotFoundException();
        }

        $referral->setLead($lead);
        $referral->setType($type);

        if ($type->isOrganizationRequired()) {

            $organizationId = $newReferral['organization_id'] ?? 0;

            /** @var OrganizationRepository $organizationRepo */
            $organizationRepo = $this->em->getRepository(Organization::class);

            /** @var Organization $organization */
            $organization = $organizationRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Organization::class), $organizationId);

            if ($organization === null) {
                throw new OrganizationNotFoundException();
            }

            $referral->setOrganization($organization);

            $this->validate($referral, null, [$organizationRequiredValidationGroup]);
        } else {
            $referral->setOrganization(null);
        }

        if ($type->isRepresentativeRequired()) {
            $emails = !empty($newReferral['emails']) ? $newReferral['emails'] : [];
            $notes = $newReferral['notes'] ?? '';

            $referral->setFirstName($newReferral['first_name']);
            $referral->setLastName($newReferral['last_name']);
            $referral->setNotes($notes);
            $referral->setEmails($emails);
            $referral->setPhones($referralService->savePhones($referral, $newReferral['phones'] ?? []));

            $this->validate($referral, null, [$representativeRequiredValidationGroup]);
        } else {
            $referral->setFirstName(null);
            $referral->setLastName(null);
            $referral->setNotes(null);
            $referral->setEmails([]);
            $referral->setPhones($referralService->savePhones($referral, []));
        }

        $this->em->persist($referral);
    }

    /**
     * @param Lead $lead
     * @param $isEdited
     */
    private function createLeadInitialContactActivity(Lead $lead, $isEdited)
    {
        /** @var ActivityTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ActivityType::class);

        /** @var ActivityType $type */
        $type = $typeRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), 1);

        if ($type === null) {
            throw new ActivityTypeNotFoundException();
        }

        $date = $isEdited ? new \DateTime('now') : $lead->getInitialContactDate();

        $activity = new Activity();
        $activity->setLead($lead);
        $activity->setType($type);
        $activity->setOwnerType(ActivityOwnerType::TYPE_LEAD);
        $activity->setDate($date);
        $activity->setStatus($type->getDefaultStatus());
        $activity->setTitle($type->getTitle());
        $activity->setNotes($type->getTitle());
        $activity->setAssignTo(null);
        $activity->setDueDate(null);
        $activity->setReminderDate(null);
        $activity->setFacility(null);
        $activity->setReferral(null);
        $activity->setOrganization(null);

        $this->validate($activity, null, ['api_lead_lead_activity_add']);

        $this->em->persist($activity);
    }

    /**
     * @param Lead $lead
     */
    private function createLeadStateChangeReasonActivity(Lead $lead)
    {
        /** @var ActivityTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ActivityType::class);

        /** @var ActivityType $type */
        $type = $typeRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), 11);

        if ($type === null) {
            throw new ActivityTypeNotFoundException();
        }

        $date = $lead->getStateEffectiveDate() ?? new \DateTime('now');
        $notes = $lead->getStateChangeReason() ? $lead->getStateChangeReason()->getTitle() : '';

        $activity = new Activity();
        $activity->setLead($lead);
        $activity->setType($type);
        $activity->setOwnerType(ActivityOwnerType::TYPE_LEAD);
        $activity->setDate($date);
        $activity->setStatus($type->getDefaultStatus());
        $activity->setTitle($type->getTitle());
        $activity->setNotes($notes);
        $activity->setAssignTo(null);
        $activity->setDueDate(null);
        $activity->setReminderDate(null);
        $activity->setFacility(null);
        $activity->setReferral(null);
        $activity->setOrganization(null);

        $this->validate($activity, null, ['api_lead_lead_activity_add']);

        $this->em->persist($activity);
    }

    /**
     * @param Lead $lead
     * @param RouterInterface $router
     */
    private function leadAddChangeLog(Lead $lead, RouterInterface $router)
    {
        $name = $lead->getFirstName() .' '. $lead->getLastName();
        $id = $lead->getId();
        $ownerName = $lead->getOwner() ? ucfirst($lead->getOwner()->getFullName()) : '';
        $userName = $lead->getUpdatedBy() ? ucfirst($lead->getUpdatedBy()->getFullName()) : '';
        $primaryFacility = $lead->getPrimaryFacility() ? $lead->getPrimaryFacility()->getName() : '';
        $date = new \DateTime('now');

        $content = [
            'lead_name' => $name,
            'lead_id' => $id,
            'owner' => $ownerName,
            'primary_facility' => $primaryFacility,
            'user_name' => $userName,
            'created_at' => $date->format('m/d/Y H:i')
        ];

        $changeLog = new ChangeLog();
        $changeLog->setType(ChangeLogType::TYPE_NEW_LEAD);
        $changeLog->setContent($content);
        $changeLog->setOwner($lead->getOwner());
        $changeLog->setSpace($lead->getOwner()->getSpace());

        $this->validate($changeLog, null, ['api_admin_change_log_add']);

        $this->em->persist($changeLog);
    }

    /**
     * @param $oldState
     * @param $newState
     * @param Lead $lead
     * @param RouterInterface $router
     */
    private function leadStateEditChangeLog($oldState, $newState, Lead $lead, RouterInterface $router)
    {
        $name = $lead->getFirstName() .' '. $lead->getLastName()  ;
        $id = $lead->getId();
        $ownerName = $lead->getOwner() ? ucfirst($lead->getOwner()->getFullName()) : '';
        $userName = $lead->getUpdatedBy() ? ucfirst($lead->getUpdatedBy()->getFullName()) : '';
        $primaryFacility = $lead->getPrimaryFacility() ? $lead->getPrimaryFacility()->getName() : '';

        $oldState = State::getTypes()[$oldState];
        $newState = State::getTypes()[$newState];
        $date = new \DateTime('now');

        $content = [
            'lead_name' => $name,
            'lead_id' => $id,
            'owner' => $ownerName,
            'primary_facility' => $primaryFacility,
            'old_state' => $oldState,
            'new_state' => $newState,
            'user_name' => $userName,
            'created_at' => $date->format('m/d/Y H:i')
        ];

        $changeLog = new ChangeLog();
        $changeLog->setType(ChangeLogType::TYPE_LEAD_UPDATED);
        $changeLog->setContent($content);
        $changeLog->setOwner($lead->getOwner());
        $changeLog->setSpace($lead->getOwner()->getSpace());

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

            /** @var LeadRepository $repo */
            $repo = $this->em->getRepository(Lead::class);

            /** @var Lead $entity */
            $entity = $repo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $id);

            if ($entity === null) {
                throw new LeadNotFoundException();
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
                throw new LeadNotFoundException();
            }

            /** @var LeadRepository $repo */
            $repo = $this->em->getRepository(Lead::class);

            $leads = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $ids);

            if (empty($leads)) {
                throw new LeadNotFoundException();
            }

            /**
             * @var Lead $lead
             */
            foreach ($leads as $lead) {
                $this->em->remove($lead);
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
            throw new LeadNotFoundException();
        }

        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $ids);

        if (empty($entities)) {
            throw new LeadNotFoundException();
        }

        return $this->getRelatedData(Lead::class, $entities);
    }
}
