<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\CareTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ContactNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ContactOrganizationChangedException;
use App\Api\V1\Common\Service\Exception\Lead\FunnelStageNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadFunnelStageNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadRpPhoneOrEmailNotBeBlankException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferrerTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\TemperatureNotFoundException;
use App\Api\V1\Common\Service\Exception\PaymentSourceNotFoundException;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SubjectNotBeBlankException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ChangeLog;
use App\Entity\CityStateZip;
use App\Entity\EmailLog;
use App\Entity\Facility;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\CareType;
use App\Entity\Lead\Contact;
use App\Entity\Lead\FunnelStage;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadFunnelStage;
use App\Entity\Lead\LeadTemperature;
use App\Entity\Lead\Organization;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferrerType;
use App\Entity\Lead\Temperature;
use App\Entity\PaymentSource;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use App\Model\ChangeLogType;
use App\Model\Lead\ActivityOwnerType;
use App\Model\Lead\State;
use App\Repository\CityStateZipRepository;
use App\Repository\FacilityRepository;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\CareTypeRepository;
use App\Repository\Lead\ContactRepository;
use App\Repository\Lead\FunnelStageRepository;
use App\Repository\Lead\LeadFunnelStageRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\LeadTemperatureRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\ReferrerTypeRepository;
use App\Repository\Lead\TemperatureRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class LeadService
 * @package App\Api\V1\Admin\Service
 */
class LeadService extends BaseService implements IGridService
{
    /**
     * @var ActivityService
     */
    private $activityService;

    /**
     * @param ActivityService $activityService
     */
    public function setActivityService(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params): void
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        $all = false;
        if (!empty($params) && isset($params[0]['all'])) {
            $all = true;
        }

        $facilityEntityGrants = $this->grantService->getCurrentUserEntityGrants(Facility::class);

        $userId = null;
        if ($facilityEntityGrants !== null || (!empty($params) && isset($params[0]['my']) && !empty($params[0]['user_id']))) {
            $userId = $params[0]['user_id'];
        }

        $repo->search($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $queryBuilder, $all, $userId, $facilityEntityGrants);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function list($params)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        $isParams = !empty($params);

        // for hot leads link in facility dashboard
        if ($isParams && !empty($params[0]['facility_id']) && !empty($params[0]['date_from']) && !empty($params[0]['date_to'])) {
            $dateFrom = new \DateTime($params[0]['date_from']);
            $dateTo = new \DateTime($params[0]['date_to']);
            $facilityId = $params[0]['facility_id'];

            /** @var LeadTemperatureRepository $leadTemperatureRepo */
            $leadTemperatureRepo = $this->em->getRepository(LeadTemperature::class);
            $hotLeadTemperatures = $leadTemperatureRepo->getHotLeadsForFacilityDashboard($currentSpace, null, $dateFrom, $dateTo, $facilityId);

            $ids = array_map(static function ($item) {
                return $item['leadId'];
            }, $hotLeadTemperatures);

            return $repo->list($currentSpace, null, false, false, null, null, null, $ids);
        } else {
            $free = false;
            if ($isParams && isset($params[0]['free'])) {
                $free = true;
            }

            $all = false;
            if ($isParams && isset($params[0]['all'])) {
                $all = true;
            }

            $facilityEntityGrants = $this->grantService->getCurrentUserEntityGrants(Facility::class);

            $userId = null;
            if ($facilityEntityGrants !== null || ($isParams && isset($params[0]['my']) && !empty($params[0]['user_id']))) {
                $userId = $params[0]['user_id'];
            }

            $contactId = null;
            if (!empty($params) && !empty($params[0]['contact_id'])) {
                $contactId = $params[0]['contact_id'];
                $free = false;
            }

            return $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $all, $free, $userId, $facilityEntityGrants, $contactId);
        }
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

            if (!empty($params['facilities'])) {
                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, null, $facilityIds);

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

                $this->saveReferral($lead, $newReferral);
            }

            // Creating lead funnel stage
            $funnelStageId = $params['funnel_stage_id'] ?? 0;
            $this->createLeadFunnelStage($lead, $funnelStageId);

            // Creating lead temperature
            $temperatureId = $params['temperature_id'] ?? 0;
            $this->createLeadTemperature($lead, $temperatureId);

            // Creating initial contact activity
            $this->createLeadInitialContactActivity($lead, false);

            $this->em->flush();

            // Creating change log
            $changeLog = $this->leadAddChangeLog($lead);

            $this->em->flush();

            if ($changeLog !== null) {
                $this->sendNewLeadChangeLogNotification($changeLog, $params['base_url']);
            }

            $this->em->getConnection()->commit();

            $insert_id = $lead->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    /**
     * @param array $params
     * @param $baseUrl
     * @return int|null
     * @throws \Exception
     */
    public function addWebLeadFromCommand(array $params, $baseUrl): ?int
    {
        $insert_id = null;
        try {
            $this->em->getConnection()->beginTransaction();

            $spaces = $this->em->getRepository(Space::class)->findAll();

            $currentSpace = null;
            if (!empty($spaces)) {
                $currentSpace = $spaces[0];
            }

            if ($currentSpace === null) {
                throw new SpaceNotFoundException();
            }

            $subject = null;
                $isBookATour = false;
            if (!empty($params['Subject'])) {
                $subject = $params['Subject'];
                if (stripos($subject, 'book') !== false) {
                    $isBookATour = true;
                }
            }

            if ($subject === null) {
                throw new SubjectNotBeBlankException();
            }

            $lead = new Lead();
            $lead->setWebLead(true);

            $facility = null;
            if (!empty($params['From'])) {
                $from = explode(' <', $params['From']);
                $potentialName = $from[0];

                //if potential name = 'CiminoCare' then facility = null
                if ($potentialName !== 'CiminoCare') {
                    /** @var FacilityRepository $facilityRepo */
                    $facilityRepo = $this->em->getRepository(Facility::class);

                    $facilities = $facilityRepo->findBy(['space' => $currentSpace]);

                    if (!empty($facilities)) {
                        /** @var Facility $value */
                        foreach ($facilities as $value) {
                            if (in_array($potentialName, $value->getPotentialNames(), false)) {
                                $facility = $value;
                                break;
                            }
                        }
                    }

                    if ($facility === null) {
                        throw new FacilityNotFoundException();
                    }
                }

                $lead->setPrimaryFacility($facility);
            } else {
                $lead->setPrimaryFacility(null);
            }

            $lead->setFirstName('Unknown');
            $lead->setLastName('Unknown');
            $lead->setCareType(null);
            $lead->setPaymentType(null);

            $roleName = 'Facility Admin';
            /** @var RoleRepository $roleRepo */
            $roleRepo = $this->em->getRepository(Role::class);

            /** @var Role $role */
            $role = $roleRepo->findOneBy(['name' => strtolower($roleName)]);

            if ($role === null) {
                throw new RoleNotFoundException();
            }

            /** @var UserRepository $ownerRepo */
            $ownerRepo = $this->em->getRepository(User::class);
            $userFacilityIds = $ownerRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, [$role->getId()]);

            $ownerId = 0;
            if (!empty($userFacilityIds)) {
                foreach ($userFacilityIds as $userFacilityId) {
                    if ($userFacilityId['facilityIds'] === null) {
                        $ownerId = $userFacilityId['id'];
                        break;
                    }

                    if ($facility !== null && $userFacilityId['facilityIds'] !== null) {
                        $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                        if (\in_array($facility->getId(), $explodedUserFacilityIds, false)) {
                            $ownerId = $userFacilityId['id'];
                            break;
                        }
                    }
                }
            }

            /** @var User $owner */
            $owner = $ownerRepo->getOne($currentSpace, null, $ownerId);

            if ($owner === null) {
                throw new UserNotFoundException();
            }

            $lead->setOwner($owner);
            $lead->setCreatedBy($owner);
            $lead->setUpdatedBy($owner);

            $lead->setState(State::TYPE_OPEN);
            $lead->setInitialContactDate(new \DateTime('now'));

            $rpFirstName = '';
            $rpLastName = '';
            if (!empty($params['Name'])) {
                $name = explode(' ', $params['Name']);
                $rpFirstName = $rpLastName = array_pop($name);
                if (!empty($name)) {
                    $rpFirstName = implode(' ', $name);
                }
            }
            $lead->setResponsiblePersonFirstName($rpFirstName);
            $lead->setResponsiblePersonLastName($rpLastName);
            $lead->setResponsiblePersonAddress1(null);
            $lead->setResponsiblePersonAddress2(null);
            $lead->setResponsiblePersonCsz(null);
            $lead->setResponsiblePersonCsz(null);

            if (!empty($params['Phone'])) {
                if (!empty($params['Message']) && stripos($params['Message'], $params['Phone']) !== false) {
                    $phone = null;
                } else {
                    $phone = $this->formatPhoneUs($params['Phone']);
                }

                $lead->setResponsiblePersonPhone($phone);
            } else {
                $lead->setResponsiblePersonPhone(null);
            }

            if (!empty($params['Email'])) {
                $lead->setResponsiblePersonEmail($params['Email']);
            } else {
                $lead->setResponsiblePersonEmail(null);
            }

            if ($lead->getResponsiblePersonPhone() === null && $lead->getResponsiblePersonEmail() === null) {
                throw new LeadRpPhoneOrEmailNotBeBlankException();
            }

            $notes = $params['Message'] ?? '';

            $lead->setNotes($notes);

            $this->validate($lead, null, ['api_lead_lead_add']);

            $this->em->persist($lead);

            // Creating lead funnel stage
            $funnelStageName = 'Contact';
            /** @var FunnelStageRepository $funnelStageRepo */
            $funnelStageRepo = $this->em->getRepository(FunnelStage::class);

            /** @var FunnelStage $funnelStage */
            $funnelStage = $funnelStageRepo->findOneBy(['title' => strtolower($funnelStageName), 'space' => $currentSpace]);

            if ($funnelStage === null) {
                throw new FunnelStageNotFoundException();
            }

            $this->createLeadFunnelStage($lead, $funnelStage->getId());

            // Creating lead temperature
            $temperatureName = 'None';
            /** @var TemperatureRepository $temperatureRepo */
            $temperatureRepo = $this->em->getRepository(Temperature::class);

            /** @var Temperature $temperature */
            $temperature = $temperatureRepo->findOneBy(['title' => strtolower($temperatureName), 'space' => $currentSpace]);

            if ($temperature === null) {
                throw new TemperatureNotFoundException();
            }

            $this->createLeadTemperature($lead, $temperature->getId());

            // Creating initial contact activity
            $this->createLeadInitialContactActivity($lead, false);

            $this->em->flush();

            // Creating change log
            $changeLog = $this->leadAddChangeLog($lead);

            // Creating task activity
            $this->createWebLeadTaskActivity($lead, $isBookATour);

            $this->em->flush();

            if ($changeLog !== null) {
                $this->sendNewLeadChangeLogNotification($changeLog, $baseUrl);
            }

            $this->em->getConnection()->commit();

            $insert_id = $lead->getId();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }

        return $insert_id;
    }

    private function formatPhoneUs($phone) {
        //strip out everything but numbers
        $phone = preg_replace('/\D/', '', $phone);
        $length = strlen($phone);

        switch($length) {
            case 7:
                return preg_replace('/(\d{3})(\d{4})/', '(000) $1-$2', $phone);
                break;
            case 10:
                return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $phone);
                break;
            case 11:
                return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{4})/', '($2) $3-$4', $phone);
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * @param Lead $lead
     * @param $isBookATour
     */
    private function createWebLeadTaskActivity(Lead $lead, $isBookATour)
    {
        /** @var ActivityTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ActivityType::class);

        /** @var ActivityType $type */
        $type = $typeRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), 10);

        if ($type === null) {
            throw new ActivityTypeNotFoundException();
        }

        $initialContactDate = $lead->getInitialContactDate();
        $rpFullName = $lead->getResponsiblePersonFirstName() . ' ' . $lead->getResponsiblePersonLastName();
        $notes = 'You have a new web form contact from ' . $rpFullName . '; they said ' . $lead->getNotes() . '. Please follow-up by email at ' . $lead->getResponsiblePersonEmail() . '. When you have followed up, update the Lead information in the database, and change the status of this task to Done.  You can find this Lead in your My Dashboard in the database.';
        $title = $isBookATour ? 'Book a Tour' : 'Web Contact Form Follow-up';

        $activity = new Activity();
        $activity->setLead($lead);
        $activity->setType($type);
        $activity->setOwnerType(ActivityOwnerType::TYPE_LEAD);
        $activity->setDate($initialContactDate);
        $activity->setTitle($title);
        $activity->setNotes($notes);

        if ($type->getDefaultStatus()) {
            $activity->setStatus($type->getDefaultStatus());
        }

        if ($type->isAssignTo()) {
            $activity->setAssignTo($lead->getOwner());
        }

        if ($initialContactDate !== null && $type->isDueDate()) {
            $dueDate = clone $initialContactDate;
            $activity->setDueDate($dueDate->add(new \DateInterval('P5D')));
        }

        if ($initialContactDate !== null && $type->isReminderDate()) {
            $reminderDate = clone $initialContactDate;
            $activity->setReminderDate($reminderDate->add(new \DateInterval('P2D')));
        }

        $activity->setFacility(null);
        $activity->setReferral(null);
        $activity->setOrganization(null);

        if ($this->grantService->getCurrentSpace() === null) {
            $activity->setCreatedBy($lead->getOwner());
            $activity->setUpdatedBy($lead->getOwner());
        }

        $this->validate($activity, null, ['api_lead_lead_activity_add']);

        $this->em->persist($activity);

        if ($activity->getType() !== null && $activity->getType()->isAssignTo()) {
            $this->activityService->taskActivityAddChangeLog($activity);
        }
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

            /** @var LeadFunnelStageRepository $stageRepo */
            $stageRepo = $this->em->getRepository(LeadFunnelStage::class);
            /** @var LeadFunnelStage $lastStage */
            $lastStage = $stageRepo->getLastAction($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(LeadFunnelStage::class), $id);

            if ($lastStage === null) {
                throw new LeadFunnelStageNotFoundException();
            }

            if ($lastStage->getStage() === null) {
                throw new FunnelStageNotFoundException();
            }

            if ($lastStage->getStage()->isOpen()) {
                $state = State::TYPE_OPEN;
            } else {
                $state = State::TYPE_CLOSED;
            }

            $entity->setState($state);

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

            if (!empty($params['facilities'])) {
                $facilityIds = array_unique($params['facilities']);
                $facilities = $facilityRepo->findByIds($currentSpace, null, $facilityIds);

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

                $this->saveReferral($entity, $newReferral);
            }

            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();

            $leadChangeSet = $this->em->getUnitOfWork()->getEntityChangeSet($entity);

            if (!empty($leadChangeSet) && array_key_exists('state', $leadChangeSet)) {
                if ($leadChangeSet['state']['0'] === State::TYPE_CLOSED && $leadChangeSet['state']['1'] === State::TYPE_OPEN) {
                    $this->createLeadInitialContactActivity($entity, true);
                } elseif ($leadChangeSet['state']['0'] === State::TYPE_OPEN && $leadChangeSet['state']['1'] === State::TYPE_CLOSED) {
                    $this->createLeadStateChangeActivity($entity, $lastStage);
                }

                $this->leadStateEditChangeLog($leadChangeSet['state']['0'], $leadChangeSet['state']['1'], $entity);
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
     * @param array $newReferral
     */
    private function saveReferral(Lead $lead, array $newReferral)
    {
        $oldReferral = $lead->getReferral();

        if ($oldReferral !== null) {
            $organizationRequiredValidationGroup = 'api_lead_referral_organization_required_edit';
            $representativeRequiredValidationGroup = 'api_lead_referral_representative_required_edit';

            $referral = $oldReferral;
        } else {
            $organizationRequiredValidationGroup = 'api_lead_referral_organization_required_add';
            $representativeRequiredValidationGroup = 'api_lead_referral_representative_required_add';

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

            $notes = $newReferral['notes'] ?? '';
            $contactId = $newReferral['contact_id'] ?? 0;

            /** @var ContactRepository $contactRepo */
            $contactRepo = $this->em->getRepository(Contact::class);

            /** @var Contact $contact */
            $contact = $contactRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Contact::class), $contactId);

            if ($contact === null) {
                throw new ContactNotFoundException();
            }

            if ($referral->getOrganization() !== null && $contact->getOrganization() !== null && $referral->getOrganization()->getId() !== $contact->getOrganization()->getId()) {
                throw new ContactOrganizationChangedException();
            }

            $referral->setContact($contact);
            $referral->setNotes($notes);

            $this->validate($referral, null, [$representativeRequiredValidationGroup]);
        } else {
            $referral->setContact(null);
            $referral->setNotes('');
        }

        $this->em->persist($referral);
    }

    /**
     * @param Lead $lead
     * @param $funnelStageId
     */
    private function createLeadFunnelStage(Lead $lead, $funnelStageId)
    {
        /** @var FunnelStageRepository $funnelStageRepo */
        $funnelStageRepo = $this->em->getRepository(FunnelStage::class);
        /** @var FunnelStage $funnelStage */
        $funnelStage = $funnelStageRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $funnelStageId);

        if ($funnelStage === null) {
            throw new FunnelStageNotFoundException();
        }

        $leadFunnelStage = new LeadFunnelStage();
        $leadFunnelStage->setLead($lead);
        $leadFunnelStage->setStage($funnelStage);
        $leadFunnelStage->setReason(null);
        $leadFunnelStage->setDate($lead->getInitialContactDate());
        $leadFunnelStage->setNotes($funnelStage->getTitle());

        if ($this->grantService->getCurrentSpace() === null) {
            $leadFunnelStage->setCreatedBy($lead->getOwner());
            $leadFunnelStage->setUpdatedBy($lead->getOwner());
        }

        $this->validate($leadFunnelStage, null, ['api_lead_lead_funnel_stage_add']);

        $this->em->persist($leadFunnelStage);

        if ($funnelStage->isOpen()) {
            $state = State::TYPE_OPEN;
        } else {
            $state = State::TYPE_CLOSED;
        }

        $lead->setState($state);
        $this->em->persist($lead);
    }

    /**
     * @param Lead $lead
     * @param $temperatureId
     */
    private function createLeadTemperature(Lead $lead, $temperatureId)
    {
        /** @var TemperatureRepository $temperatureRepo */
        $temperatureRepo = $this->em->getRepository(Temperature::class);
        /** @var Temperature $temperature */
        $temperature = $temperatureRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), $temperatureId);

        if ($temperature === null) {
            throw new TemperatureNotFoundException();
        }

        $leadTemperature = new LeadTemperature();
        $leadTemperature->setLead($lead);
        $leadTemperature->setTemperature($temperature);
        $leadTemperature->setDate($lead->getInitialContactDate());
        $leadTemperature->setNotes($temperature->getTitle());

        if ($this->grantService->getCurrentSpace() === null) {
            $leadTemperature->setCreatedBy($lead->getOwner());
            $leadTemperature->setUpdatedBy($lead->getOwner());
        }

        $this->validate($leadTemperature, null, ['api_lead_lead_temperature_add']);

        $this->em->persist($leadTemperature);
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

        if ($this->grantService->getCurrentSpace() === null) {
            $activity->setCreatedBy($lead->getOwner());
            $activity->setUpdatedBy($lead->getOwner());
        }

        $this->validate($activity, null, ['api_lead_lead_activity_add']);

        $this->em->persist($activity);
    }

    /**
     * @param Lead $lead
     * @param LeadFunnelStage $lastStage
     */
    private function createLeadStateChangeActivity(Lead $lead, LeadFunnelStage $lastStage)
    {
        /** @var ActivityTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ActivityType::class);

        /** @var ActivityType $type */
        $type = $typeRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ActivityType::class), 11);

        if ($type === null) {
            throw new ActivityTypeNotFoundException();
        }

        $date = $lastStage->getDate() ?? new \DateTime('now');
        $notes = $lastStage->getNotes() ?? '';

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
     * @return ChangeLog
     */
    private function leadAddChangeLog(Lead $lead): ChangeLog
    {
        $name = $lead->getFirstName() . ' ' . $lead->getLastName();
        $id = $lead->getId();
        $ownerName = $lead->getOwner() ? ucfirst($lead->getOwner()->getFullName()) : '';
        $userName = $lead->getUpdatedBy() !== null ? ucfirst($lead->getUpdatedBy()->getFullName()) : '';
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

        if ($this->grantService->getCurrentSpace() === null) {
            $changeLog->setCreatedBy($lead->getOwner());
            $changeLog->setUpdatedBy($lead->getOwner());
        }

        $this->validate($changeLog, null, ['api_admin_change_log_add']);

        $this->em->persist($changeLog);

        return $changeLog;
    }

    /**
     * @param ChangeLog $changeLog
     * @param $baseUrl
     */
    public function sendNewLeadChangeLogNotification(ChangeLog $changeLog, $baseUrl): void
    {
        $emails = [];
        $logs = [];

        if ($changeLog->getOwner() !== null) {
            $emails[] = $changeLog->getOwner()->getEmail();
        }

        $activityType = '';
        $title = $changeLog->getContent()['lead_name'];
        $action = ChangeLogType::getTypes()[ChangeLogType::TYPE_NEW_LEAD];
        $date = $changeLog->getCreatedAt()->format('m/d/Y H:i');

        $logs[] = [
            'type' => $changeLog->getType(),
            'content' => $changeLog->getContent(),
            'activity_type' => $activityType,
            'title' => strip_tags($title),
            'action' => $action,
            'date' => $date,
        ];

        if (!empty($emails)) {
            $spaceName = '';
            if ($changeLog->getSpace() !== null) {
                $spaceName = $changeLog->getSpace()->getName();
            }

            $subject = 'Leads System User Activity for ' . $changeLog->getCreatedAt()->format('m/d/Y');

            $body = $this->container->get('templating')->render('@api_notification/new-lead.email.html.twig', array(
                'baseUrl' => $baseUrl,
                'logs' => $logs,
                'subject' => $subject
            ));

            $status = $this->mailer->sendNotification($emails, $subject, $body, $spaceName);

            $emailLog = new EmailLog();
            $emailLog->setSuccess($status);
            $emailLog->setSubject($subject);
            $emailLog->setSpace($spaceName);
            $emailLog->setEmails($emails);

            $this->em->persist($emailLog);
            $this->em->flush();
        }
    }

    /**
     * @param $oldState
     * @param $newState
     * @param Lead $lead
     */
    private function leadStateEditChangeLog($oldState, $newState, Lead $lead)
    {
        $name = $lead->getFirstName() . ' ' . $lead->getLastName();
        $id = $lead->getId();
        $ownerName = $lead->getOwner() ? ucfirst($lead->getOwner()->getFullName()) : '';
        $userName = $lead->getUpdatedBy() !== null ? ucfirst($lead->getUpdatedBy()->getFullName()) : '';
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
