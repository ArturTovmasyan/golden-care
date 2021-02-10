<?php

namespace App\Api\V1\Lead\Service;

use App\Api\V1\Admin\Service\ResidentAdmissionService;
use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CareLevelNotFoundException;
use App\Api\V1\Common\Service\Exception\CityStateZipNotFoundException;
use App\Api\V1\Common\Service\Exception\FacilityNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityStatusNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ActivityTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\CareTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ContactNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ContactOrganizationChangedException;
use App\Api\V1\Common\Service\Exception\Lead\CurrentResidenceNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\FunnelStageNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadFunnelStageNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\LeadRpPhoneOrEmailNotBeBlankException;
use App\Api\V1\Common\Service\Exception\NameNotBeBlankException;
use App\Api\V1\Common\Service\Exception\Lead\OrganizationNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\QualificationRequirementNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\ReferrerTypeNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\StageChangeReasonNotFoundException;
use App\Api\V1\Common\Service\Exception\Lead\TemperatureNotFoundException;
use App\Api\V1\Common\Service\Exception\PaymentSourceNotFoundException;
use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SalutationNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Service\Exception\SubjectNotBeBlankException;
use App\Api\V1\Common\Service\Exception\UserNotFoundException;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\CareLevel;
use App\Entity\ChangeLog;
use App\Entity\CityStateZip;
use App\Entity\EmailLog;
use App\Entity\Facility;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ActivityStatus;
use App\Entity\Lead\ActivityType;
use App\Entity\Lead\CareType;
use App\Entity\Lead\Contact;
use App\Entity\Lead\CurrentResidence;
use App\Entity\Lead\FunnelStage;
use App\Entity\Lead\Hobby;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadFunnelStage;
use App\Entity\Lead\LeadQualificationRequirement;
use App\Entity\Lead\LeadTemperature;
use App\Entity\Lead\Organization;
use App\Entity\Lead\QualificationRequirement;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferrerType;
use App\Entity\Lead\StageChangeReason;
use App\Entity\Lead\Temperature;
use App\Entity\Lead\WebEmail;
use App\Entity\PaymentSource;
use App\Entity\Resident;
use App\Entity\ResidentAdmission;
use App\Entity\Role;
use App\Entity\Salutation;
use App\Entity\Space;
use App\Entity\User;
use App\Model\ChangeLogType;
use App\Model\GroupType;
use App\Model\Lead\ActivityOwnerType;
use App\Model\Lead\Qualified;
use App\Model\Lead\State;
use App\Repository\CareLevelRepository;
use App\Repository\CityStateZipRepository;
use App\Repository\FacilityRepository;
use App\Repository\Lead\ActivityStatusRepository;
use App\Repository\Lead\ActivityTypeRepository;
use App\Repository\Lead\CareTypeRepository;
use App\Repository\Lead\ContactRepository;
use App\Repository\Lead\CurrentResidenceRepository;
use App\Repository\Lead\FunnelStageRepository;
use App\Repository\Lead\HobbyRepository;
use App\Repository\Lead\LeadFunnelStageRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\LeadTemperatureRepository;
use App\Repository\Lead\OrganizationRepository;
use App\Repository\Lead\QualificationRequirementRepository;
use App\Repository\Lead\ReferrerTypeRepository;
use App\Repository\Lead\StageChangeReasonRepository;
use App\Repository\Lead\TemperatureRepository;
use App\Repository\Lead\LeadQualificationRequirementRepository;
use App\Repository\PaymentSourceRepository;
use App\Repository\RoleRepository;
use App\Repository\SalutationRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\ConnectionException;
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

        $open = false;
        if (!empty($params) && isset($params[0]['open'])) {
            $open = true;
        }

        $closed = false;
        if (!empty($params) && isset($params[0]['closed'])) {
            $closed = true;
        }

        $both = false;
        if (!empty($params) && isset($params[0]['both'])) {
            $both = true;
        }

        if ($both === true) {
            $open = false;
            $closed = false;
        }

        if ($closed === true) {
            $open = false;
            $both = false;
        }

        if ($open === true) {
            $closed = false;
            $both = false;
        }

        if ($open === false && $closed === false && $both === false) {
            $open = true;
        }

        $spam = false;
        if (!empty($params) && isset($params[0]['spam'])) {
            $spam = true;
        }

        $facilityEntityGrants = $this->grantService->getCurrentUserEntityGrants(Facility::class);

        $userId = null;
        if ($facilityEntityGrants !== null || (!empty($params) && isset($params[0]['my']) && !empty($params[0]['user_id']))) {
            $userId = $params[0]['user_id'];
        }

        $repo->search($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $queryBuilder, $userId, $facilityEntityGrants, $open, $closed, $spam);
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

            return $repo->list($currentSpace, null, null, null, false, false, false, null, $ids);
        } else {
            $free = false;
            if ($isParams && isset($params[0]['free'])) {
                $free = true;
            }

            $all = false;
            if ($isParams && isset($params[0]['all'])) {
                $all = true;
            }

            $spam = false;
            if (!empty($params) && isset($params[0]['spam'])) {
                $spam = true;
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

            return $repo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $userId, $facilityEntityGrants, $all, $free, $spam, $contactId, null);
        }
    }

    /**
     * @param $id
     * @param $gridData
     * @return Lead
     */
    public function getById($id, $gridData)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);
        /** @var Lead $entity */
        $entity = $repo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $id);

        $this->setPreviousAndNextItemIdsFromGrid($entity, $gridData);

        return $entity;
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

            if (!empty($params['care_level_id'])) {
                /** @var CareLevelRepository $careLevelRepo */
                $careLevelRepo = $this->em->getRepository(CareLevel::class);

                /** @var CareLevel $careLevel */
                $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $params['care_level_id']);

                if ($careLevel === null) {
                    throw new CareLevelNotFoundException();
                }

                $lead->setCareLevel($careLevel);
            } else {
                $lead->setCareLevel(null);
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

            if (!empty($params['birthday'])) {
                $birthday = new \DateTime($params['birthday']);
            } else {
                $birthday = null;
            }

            $lead->setBirthday($birthday);

            $spouseName = !empty($params['spouse_name']) && !ctype_space($params['spouse_name'])? $params['spouse_name'] : null;

            $lead->setSpouseName($spouseName);

            if (!empty($params['current_residence_id'])) {
                /** @var CurrentResidenceRepository $currentResidenceRepo */
                $currentResidenceRepo = $this->em->getRepository(CurrentResidence::class);

                /** @var CurrentResidence $currentResidence */
                $currentResidence = $currentResidenceRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $params['current_residence_id']);

                if ($currentResidence === null) {
                    throw new CurrentResidenceNotFoundException();
                }

                $lead->setCurrentResidence($currentResidence);
            } else {
                $lead->setCurrentResidence(null);
            }

            if (!empty($params['hobbies'])) {
                /** @var HobbyRepository $hobbyRepo */
                $hobbyRepo = $this->em->getRepository(Hobby::class);

                $hobbyIds = array_unique($params['hobbies']);
                $hobbies = $hobbyRepo->findByIds($currentSpace, null, $hobbyIds);

                if (!empty($hobbies)) {
                    $lead->setHobbies($hobbies);
                }
            }

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

            $leadQualificationRequirements = $this->saveQualificationRequirements($currentSpace, $lead, $params['qualifications'] ?? []);

            $lead->setLeadQualificationRequirements($leadQualificationRequirements);

            // Set Qualified State
            $lead->setQualified($this->saveQualified($leadQualificationRequirements));

            $this->validate($lead, null, ['api_lead_lead_add']);

            $this->em->persist($lead);

            // Save Referral
            $referral = null;
            if (!empty($params['referral'])) {
                $newReferral = $params['referral'];

                $referral = $this->saveReferral($lead, $newReferral);
            }

            // Creating lead funnel stage
            $funnelStageId = $params['funnel_stage_id'] ?? 0;
            $this->createLeadFunnelStage($lead, $funnelStageId);

            // Creating lead temperature
            $temperatureId = $params['temperature_id'] ?? 0;
            $this->createLeadTemperature($lead, $temperatureId);

            // Creating initial contact activity
            $this->createLeadInitialContactActivity($lead, false);

            // Creating Referral Contact activity
            if ($referral !== null && $referral->getContact() !== null) {
                $this->createLeadReferralContactActivity($lead, $referral->getContact());
            }

            $this->em->flush();

            // Creating change log
            $changeLog = $this->leadAddChangeLog($lead, $referral);

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
     * @param WebEmail $webEmail
     * @param $baseUrl
     * @return int|null
     * @throws ConnectionException
     */
    public function addWebLeadFromWebEmail(WebEmail $webEmail, $baseUrl): ?int
    {
        $insert_id = null;

        try {
            $this->em->getConnection()->beginTransaction();

            $currentSpace = $webEmail->getSpace();
            $facility = $webEmail->getFacility();

            $lead = new Lead();
            $lead->setWebLead(true);
            $lead->setSpamUpdated($webEmail->getDate());
            $lead->setSpam(false);
            $lead->setPrimaryFacility($facility);
            $lead->setFirstName('Not');
            $lead->setLastName('Provided');
            $lead->setCareType(null);
            $lead->setCareLevel(null);
            $lead->setPaymentType(null);
            // Set Qualified State
            $lead->setQualified(Qualified::TYPE_NOT_SURE);

            $roleName = $facility !== null ? 'Marketing' : 'Corporate Marketing';
            /** @var RoleRepository $roleRepo */
            $roleRepo = $this->em->getRepository(Role::class);

            /** @var Role $role */
            $role = $roleRepo->findOneBy(['name' => strtolower($roleName)]);

            if ($role === null) {
                throw new RoleNotFoundException();
            }

            /** @var UserRepository $ownerRepo */
            $ownerRepo = $this->em->getRepository(User::class);

            $ownerId = 0;
            if ($facility !== null) {
                $userFacilityIds = $ownerRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, [$role->getId()]);

                if (!empty($userFacilityIds)) {
                    foreach ($userFacilityIds as $userFacilityId) {
                        if ($userFacilityId['facilityIds'] === null) {
                            $ownerId = $userFacilityId['id'];
                            break;
                        }

                        if ($userFacilityId['facilityIds'] !== null) {
                            $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                            if (\in_array($facility->getId(), $explodedUserFacilityIds, false)) {
                                $ownerId = $userFacilityId['id'];
                                break;
                            }
                        }
                    }
                } else {
                    $roleName = 'Facility Admin';

                    /** @var Role $role */
                    $role = $roleRepo->findOneBy(['name' => strtolower($roleName)]);

                    if ($role === null) {
                        throw new RoleNotFoundException();
                    }

                    $userFacilityIds = $ownerRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, [$role->getId()]);

                    if (!empty($userFacilityIds)) {
                        foreach ($userFacilityIds as $userFacilityId) {
                            if ($userFacilityId['facilityIds'] === null) {
                                $ownerId = $userFacilityId['id'];
                                break;
                            }

                            if ($userFacilityId['facilityIds'] !== null) {
                                $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                                if (\in_array($facility->getId(), $explodedUserFacilityIds, false)) {
                                    $ownerId = $userFacilityId['id'];
                                    break;
                                }
                            }
                        }
                    }
                }

                /** @var User $owner */
                $owner = $ownerRepo->getOne($currentSpace, null, $ownerId);
            } else {
                $owner = $ownerRepo->getEnabledUserByRoles($currentSpace, null, [$role->getId()]);
            }

            if ($owner === null) {
                throw new UserNotFoundException();
            }

            $lead->setOwner($owner);
            $lead->setCreatedBy($owner);
            $lead->setUpdatedBy($owner);
            $lead->setState(State::TYPE_OPEN);
            $lead->setInitialContactDate($webEmail->getDate());
            $lead->setBirthday(null);
            $lead->setSpouseName(null);
            $lead->setCurrentResidence(null);

            if (!empty($webEmail->getName())) {
                $name = explode(' ', $webEmail->getName());
                $rpFirstName = $rpLastName = array_pop($name);
                if (!empty($name)) {
                    $rpFirstName = implode(' ', $name);
                }
            } else {
                throw new NameNotBeBlankException();
            }
            $lead->setResponsiblePersonFirstName($rpFirstName);
            $lead->setResponsiblePersonLastName($rpLastName);
            $lead->setResponsiblePersonAddress1(null);
            $lead->setResponsiblePersonAddress2(null);
            $lead->setResponsiblePersonCsz(null);

            if (!empty($webEmail->getPhone())) {
                $lead->setResponsiblePersonPhone($webEmail->getPhone());
            } else {
                $lead->setResponsiblePersonPhone(null);
            }

            if (!empty($webEmail->getEmail())) {
                $lead->setResponsiblePersonEmail($webEmail->getEmail());
            } else {
                $lead->setResponsiblePersonEmail(null);
            }

            if ($lead->getResponsiblePersonPhone() === null && $lead->getResponsiblePersonEmail() === null) {
                throw new LeadRpPhoneOrEmailNotBeBlankException();
            }

            $notes = !empty($webEmail->getMessage()) ? mb_strimwidth($webEmail->getMessage(), 0, 2048) : '';

            $lead->setNotes($notes);

            $this->validate($lead, null, ['api_lead_lead_add']);

            $this->em->persist($lead);

            /** @var QualificationRequirementRepository $qualificationRequirementRepo */
            $qualificationRequirementRepo = $this->em->getRepository(QualificationRequirement::class);

            $qualificationRequirements = $qualificationRequirementRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class));

            if (!empty($qualificationRequirements)) {
                /** @var QualificationRequirement $qualificationRequirement */
                foreach ($qualificationRequirements as $qualificationRequirement) {
                    $leadQualificationRequirement = new LeadQualificationRequirement();
                    $leadQualificationRequirement->setLead($lead);
                    $leadQualificationRequirement->setQualificationRequirement($qualificationRequirement);
                    $leadQualificationRequirement->setQualified(Qualified::TYPE_NOT_SURE);

                    $this->em->persist($leadQualificationRequirement);
                }
            }

            $type = $webEmail->getType();
            if ($type !== null) {
                if ($type->isRepresentativeRequired() || $type->isOrganizationRequired()) {
                    throw new ReferrerTypeNotFoundException();
                }

                $referral = new Referral();
                $referral->setLead($lead);
                $referral->setType($type);
                $referral->setOrganization(null);
                $referral->setContact(null);
                $referral->setNotes('');
                $referral->setCreatedBy($lead->getOwner());
                $referral->setUpdatedBy($lead->getOwner());

                $this->em->persist($referral);
            }

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

    /**
     * @param array $params
     * @param $baseUrl
     * @return int|null
     * @throws ConnectionException
     */
    public function addWebLeadFromCommand(array $params, $baseUrl): ?int
    {
        $insert_id = null;

        $spam = true;
        if (array_key_exists('Spam', $params)) {
            $spam = (bool)$params['Spam'];
        }

        $subject = null;
        if (!empty($params['Subject']) && (stripos($params['Subject'], 'new submission') !== false || stripos($params['Subject'], 'new form entry') !== false)) {
            $subject = $params['Subject'];
        }

        if ($subject === null) {
            throw new SubjectNotBeBlankException();
        }

        if (!$spam && stripos($subject, 'facebook ad') === false) {
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

                $now = new \DateTime('now');

                $lead = new Lead();
                $lead->setWebLead(true);
                $lead->setSpamUpdated($now);
                $lead->setSpam($spam);

                $facility = null;
                if (!empty($params['From'])) {
                    $from = explode(' <', $params['From']);
                    $potentialName = $from[0];

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

                        if ($facility === null) {
                            /** @var Facility $value */
                            foreach ($facilities as $value) {
                                if (stripos($params['Subject'], $value->getName()) !== false) {
                                    $facility = $value;
                                    break;
                                }
                            }
                        }
                    }

                    $lead->setPrimaryFacility($facility);
                } else {
                    $lead->setPrimaryFacility(null);
                }

                $lead->setFirstName('Not');
                $lead->setLastName('Provided');
                $lead->setCareType(null);
                $lead->setCareLevel(null);
                $lead->setPaymentType(null);
                // Set Qualified State
                $lead->setQualified(Qualified::TYPE_NOT_SURE);

                $roleName = $facility !== null ? 'Marketing' : 'Corporate Marketing';
                /** @var RoleRepository $roleRepo */
                $roleRepo = $this->em->getRepository(Role::class);

                /** @var Role $role */
                $role = $roleRepo->findOneBy(['name' => strtolower($roleName)]);

                if ($role === null) {
                    throw new RoleNotFoundException();
                }

                /** @var UserRepository $ownerRepo */
                $ownerRepo = $this->em->getRepository(User::class);

                $ownerId = 0;
                if ($facility !== null) {
                    $userFacilityIds = $ownerRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, [$role->getId()]);

                    if (!empty($userFacilityIds)) {
                        foreach ($userFacilityIds as $userFacilityId) {
                            if ($userFacilityId['facilityIds'] === null) {
                                $ownerId = $userFacilityId['id'];
                                break;
                            }

                            if ($userFacilityId['facilityIds'] !== null) {
                                $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                                if (\in_array($facility->getId(), $explodedUserFacilityIds, false)) {
                                    $ownerId = $userFacilityId['id'];
                                    break;
                                }
                            }
                        }
                    } else {
                        $roleName = 'Facility Admin';

                        /** @var Role $role */
                        $role = $roleRepo->findOneBy(['name' => strtolower($roleName)]);

                        if ($role === null) {
                            throw new RoleNotFoundException();
                        }

                        $userFacilityIds = $ownerRepo->getEnabledUserFacilityIdsByRoles($currentSpace, null, [$role->getId()]);

                        if (!empty($userFacilityIds)) {
                            foreach ($userFacilityIds as $userFacilityId) {
                                if ($userFacilityId['facilityIds'] === null) {
                                    $ownerId = $userFacilityId['id'];
                                    break;
                                }

                                if ($userFacilityId['facilityIds'] !== null) {
                                    $explodedUserFacilityIds = explode(',', $userFacilityId['facilityIds']);

                                    if (\in_array($facility->getId(), $explodedUserFacilityIds, false)) {
                                        $ownerId = $userFacilityId['id'];
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    /** @var User $owner */
                    $owner = $ownerRepo->getOne($currentSpace, null, $ownerId);
                } else {
                    $owner = $ownerRepo->getEnabledUserByRoles($currentSpace, null, [$role->getId()]);
                }

                if ($owner === null) {
                    throw new UserNotFoundException();
                }

                $lead->setOwner($owner);
                $lead->setCreatedBy($owner);
                $lead->setUpdatedBy($owner);

                $lead->setState(State::TYPE_OPEN);
                $lead->setInitialContactDate($now);
                $lead->setBirthday(null);
                $lead->setSpouseName(null);
                $lead->setCurrentResidence(null);

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

                $notes = !empty($params['Message']) ? mb_strimwidth($params['Message'], 0, 2048) : '';

                $lead->setNotes($notes);

                $this->validate($lead, null, ['api_lead_lead_add']);

                $this->em->persist($lead);

                /** @var QualificationRequirementRepository $qualificationRequirementRepo */
                $qualificationRequirementRepo = $this->em->getRepository(QualificationRequirement::class);

                $qualificationRequirements = $qualificationRequirementRepo->list($currentSpace, $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class));

                if (!empty($qualificationRequirements)) {
                    /** @var QualificationRequirement $qualificationRequirement */
                    foreach ($qualificationRequirements as $qualificationRequirement) {
                        $leadQualificationRequirement = new LeadQualificationRequirement();
                        $leadQualificationRequirement->setLead($lead);
                        $leadQualificationRequirement->setQualificationRequirement($qualificationRequirement);
                        $leadQualificationRequirement->setQualified(Qualified::TYPE_NOT_SURE);

                        $this->em->persist($leadQualificationRequirement);
                    }
                }

                // Add lead referral
                $referrerTypeName = 'Web Lead';
                if (stripos($subject, 'facebook ad') !== false) {
                    $referrerTypeName = 'Facebook Ad';
                }

                /** @var ReferrerTypeRepository $typeRepo */
                $typeRepo = $this->em->getRepository(ReferrerType::class);

                /** @var ReferrerType $type */
                $type = $typeRepo->findOneBy(['title' => strtolower($referrerTypeName), 'space' => $currentSpace]);

                if ($type === null) {
                    throw new ReferrerTypeNotFoundException();
                }

                if ($type->isRepresentativeRequired() || $type->isOrganizationRequired()) {
                    throw new ReferrerTypeNotFoundException();
                }

                $referral = new Referral();
                $referral->setLead($lead);
                $referral->setType($type);
                $referral->setOrganization(null);
                $referral->setContact(null);
                $referral->setNotes('');
                $referral->setCreatedBy($lead->getOwner());
                $referral->setUpdatedBy($lead->getOwner());

                $this->em->persist($referral);

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

        $notes = mb_strimwidth($notes, 0, 2048);
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

            if (!empty($params['care_level_id'])) {
                /** @var CareLevelRepository $careLevelRepo */
                $careLevelRepo = $this->em->getRepository(CareLevel::class);

                /** @var CareLevel $careLevel */
                $careLevel = $careLevelRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CareLevel::class), $params['care_level_id']);

                if ($careLevel === null) {
                    throw new CareLevelNotFoundException();
                }

                $entity->setCareLevel($careLevel);
            } else {
                $entity->setCareLevel(null);
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

            if (!empty($params['birthday'])) {
                $birthday = new \DateTime($params['birthday']);
            } else {
                $birthday = null;
            }

            $entity->setBirthday($birthday);

            $spouseName = !empty($params['spouse_name']) && !ctype_space($params['spouse_name'])? $params['spouse_name'] : null;

            $entity->setSpouseName($spouseName);

            if (!empty($params['current_residence_id'])) {
                /** @var CurrentResidenceRepository $currentResidenceRepo */
                $currentResidenceRepo = $this->em->getRepository(CurrentResidence::class);

                /** @var CurrentResidence $currentResidence */
                $currentResidence = $currentResidenceRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(CurrentResidence::class), $params['current_residence_id']);

                if ($currentResidence === null) {
                    throw new CurrentResidenceNotFoundException();
                }

                $entity->setCurrentResidence($currentResidence);
            } else {
                $entity->setCurrentResidence(null);
            }

            $hobbies = $entity->getHobbies();
            foreach ($hobbies as $hobby) {
                $entity->removeHobby($hobby);
            }

            if (!empty($params['hobbies'])) {
                /** @var HobbyRepository $hobbyRepo */
                $hobbyRepo = $this->em->getRepository(Hobby::class);

                $hobbyIds = array_unique($params['hobbies']);
                $hobbies = $hobbyRepo->findByIds($currentSpace, null, $hobbyIds);

                if (!empty($hobbies)) {
                    $entity->setHobbies($hobbies);
                }
            }

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

            $leadQualificationRequirements = $this->saveQualificationRequirements($currentSpace, $entity, $params['qualifications'] ?? []);

            $entity->setLeadQualificationRequirements($leadQualificationRequirements);

            // Set Qualified State
            $qualified = $this->saveQualified($leadQualificationRequirements);
            $entity->setQualified($qualified);

            $this->validate($entity, null, ['api_lead_lead_edit']);

            $this->em->persist($entity);

            // Save Referral
            $referral = null;
            if (!empty($params['referral'])) {
                $newReferral = $params['referral'];

                $referral = $this->saveReferral($entity, $newReferral);
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

                $this->leadStateEditChangeLog($leadChangeSet['state']['0'], $leadChangeSet['state']['1'], $entity, $referral);
            }

            //set all lead activities statuses to Done when lead state are Closed
            if ($state === State::TYPE_CLOSED && $entity->getActivities() !== null) {
                /** @var ActivityStatusRepository $activityStatusRepo */
                $activityStatusRepo = $this->em->getRepository(ActivityStatus::class);

                $activityStatus = $activityStatusRepo->getDone($currentSpace);

                if ($activityStatus === null) {
                    throw new ActivityStatusNotFoundException();
                }

                /** @var Activity $activity */
                foreach ($entity->getActivities() as $activity) {
                    if ($activity->getStatus() === null || ($activity->getStatus() !== null && !$activity->getStatus()->isDone())) {
                        $activity->setStatus($activityStatus);
                    }

                    $this->em->persist($activity);
                }
            }

            //when qualified = NO then add new Funnel Stage to change Lead state to Closed, with Reason “Not Qualified”
            if ($state === State::TYPE_OPEN && (bool)$params['close_lead'] === true && $qualified === Qualified::TYPE_NO) {
                $this->createNoQualifiedLeadFunnelStageToCloseLead($entity);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $currentSpace
     * @param Lead $lead
     * @param array $qualifications
     * @return array|null
     */
    private function saveQualificationRequirements($currentSpace, Lead $lead, array $qualifications = []): ?array
    {
        if ($lead->getId() !== null) {
            /** @var LeadQualificationRequirementRepository $leadQualificationRequirementRepo */
            $leadQualificationRequirementRepo = $this->em->getRepository(LeadQualificationRequirement::class);

            $oldQualificationRequirements = $leadQualificationRequirementRepo->getBy($lead->getId());

            foreach ($oldQualificationRequirements as $oldQualificationRequirement) {
                $this->em->remove($oldQualificationRequirement);
            }
        }

        $leadQualificationRequirements = [];

        foreach ($qualifications as $qualification) {
            $qualificationRequirementId = $qualification['qualification_requirement_id'] ?? 0;

            /** @var QualificationRequirementRepository $qualificationRequirementRepo */
            $qualificationRequirementRepo = $this->em->getRepository(QualificationRequirement::class);

            /** @var QualificationRequirement $qualificationRequirement */
            $qualificationRequirement = $qualificationRequirementRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(QualificationRequirement::class), $qualificationRequirementId);

            if ($qualificationRequirement === null) {
                throw new QualificationRequirementNotFoundException();
            }

            $qualified = isset($qualification['qualified']) ? (int)$qualification['qualified'] : 0;

            $leadQualificationRequirement = new LeadQualificationRequirement();
            $leadQualificationRequirement->setLead($lead);
            $leadQualificationRequirement->setQualificationRequirement($qualificationRequirement);
            $leadQualificationRequirement->setQualified($qualified);

            $this->em->persist($leadQualificationRequirement);

            $leadQualificationRequirements[] = $leadQualificationRequirement;
        }

        return $leadQualificationRequirements;
    }

    /**
     * @param $leadQualificationRequirements
     * @return int
     */
    private function saveQualified($leadQualificationRequirements): int
    {
        $qualified = Qualified::TYPE_NOT_SURE;

        $notQualifieds = [];
        $notSures = [];
        $qualifieds = [];
        if (!empty($leadQualificationRequirements)) {
            /** @var LeadQualificationRequirement $leadQualificationRequirement */
            foreach ($leadQualificationRequirements as $leadQualificationRequirement) {
                switch ($leadQualificationRequirement->getQualified()) {
                    case Qualified::TYPE_YES:
                        $qualifieds[] = $leadQualificationRequirement->getQualified();
                        break;
                    case Qualified::TYPE_NOT_SURE:
                        $notSures[] = $leadQualificationRequirement->getQualified();
                        break;
                    case Qualified::TYPE_NO:
                        $notQualifieds[] = $leadQualificationRequirement->getQualified();
                        break;
                    default:
                        $notSures[] = Qualified::TYPE_NOT_SURE;
                }
            }

            if (!empty($notQualifieds)) {
                $qualified = Qualified::TYPE_NO;
            }

            if (empty($notQualifieds) && !empty($notSures)) {
                $qualified = Qualified::TYPE_NOT_SURE;
            }

            if (empty($notQualifieds) && empty($notSures) && !empty($qualifieds)) {
                $qualified = Qualified::TYPE_YES;
            }
        }

        return $qualified;
    }

    /**
     * @param Lead $lead
     */
    private function createNoQualifiedLeadFunnelStageToCloseLead(Lead $lead)
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $funnelStageName = 'Closed';
        /** @var FunnelStageRepository $funnelStageRepo */
        $funnelStageRepo = $this->em->getRepository(FunnelStage::class);
        /** @var FunnelStage $funnelStage */
        $funnelStage = $funnelStageRepo->findOneBy(['title' => strtolower($funnelStageName), 'open' => false, 'space' => $currentSpace]);

        if ($funnelStage === null) {
            throw new FunnelStageNotFoundException();
        }

        $reasonName = 'Not Qualified';
        /** @var StageChangeReasonRepository $reasonRepo */
        $reasonRepo = $this->em->getRepository(StageChangeReason::class);
        /** @var StageChangeReason $reason */
        $reason = $reasonRepo->findOneBy(['title' => strtolower($reasonName), 'space' => $currentSpace]);

        if ($reason === null) {
            throw new StageChangeReasonNotFoundException();
        }

        $leadFunnelStage = new LeadFunnelStage();
        $leadFunnelStage->setLead($lead);
        $leadFunnelStage->setStage($funnelStage);
        $leadFunnelStage->setReason($reason);
        $leadFunnelStage->setDate(new \DateTime('now'));
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
     * @param $id
     * @param array $params
     * @throws \Throwable
     */
    public function editQualification($id, array $params): void
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

            $leadQualificationRequirements = $this->saveQualificationRequirements($currentSpace, $entity, $params['qualifications'] ?? []);

            $entity->setLeadQualificationRequirements($leadQualificationRequirements);

            // Set Qualified State
            $qualified = $this->saveQualified($leadQualificationRequirements);
            $entity->setQualified($qualified);

            $this->validate($entity, null, ['api_lead_lead_qualification_edit']);

            $this->em->persist($entity);

            //when qualified = NO then add new Funnel Stage to change Lead state to Closed, with Reason “Not Qualified”
            if ((bool)$params['close_lead'] === true && $qualified === Qualified::TYPE_NO && $entity->getState() === State::TYPE_OPEN) {
                $this->createNoQualifiedLeadFunnelStageToCloseLead($entity);
            }

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
     * @throws \Throwable
     */
    public function editInterest($id, array $params): void
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

            $hobbies = $entity->getHobbies();
            foreach ($hobbies as $hobby) {
                $entity->removeHobby($hobby);
            }

            if (!empty($params['hobbies'])) {
                /** @var HobbyRepository $hobbyRepo */
                $hobbyRepo = $this->em->getRepository(Hobby::class);

                $hobbyIds = array_unique($params['hobbies']);
                $hobbies = $hobbyRepo->findByIds($currentSpace, null, $hobbyIds);

                if (!empty($hobbies)) {
                    $entity->setHobbies($hobbies);
                }
            }

            $notes = $params['notes'] ?? '';

            $entity->setNotes($notes);

            $this->validate($entity, null, ['api_lead_lead_interest_edit']);

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
     * @param array $params
     * @throws \Throwable
     */
    public function addResident($id, array $params): void
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

            $salutationId = $params['salutation_id'] ?? 0;

            /** @var SalutationRepository $salutationRepo */
            $salutationRepo = $this->em->getRepository(Salutation::class);

            $salutation = $salutationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Salutation::class), $salutationId);

            if ($salutation === null) {
                throw new SalutationNotFoundException();
            }

            $gender = $params['gender'] ? (int)$params['gender'] : 0;
            $birthday = new \DateTime($params['birthday']);

            $space = $entity->getOwner() ? $entity->getOwner()->getSpace() : null;

            $resident = new Resident();
            $resident->setFirstName($entity->getFirstName());
            $resident->setLastName($entity->getLastName());
            $resident->setMiddleName('');
            $resident->setSpace($space);
            $resident->setSalutation($salutation);
            $resident->setGender($gender);
            $resident->setSsn(null);
            $resident->setBirthday($birthday);
            $resident->setPhones([]);

            $this->validate($resident, null, ['api_admin_resident_add']);

            $this->em->persist($resident);

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @param array $params
     * @param string $baseUrl
     * @throws \Exception
     */
    public function addResidentAdmission($id, ResidentAdmissionService $residentAdmissionService, array $params, string $baseUrl): void
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

            $userId = $params['user_id'] ?? 0;

            /** @var UserRepository $userRepo */
            $userRepo = $this->em->getRepository(User::class);

            /** @var User $user */
            $user = $userRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(User::class), $userId);

            if ($user === null) {
                throw new UserNotFoundException();
            }

            $salutationId = $params['salutation_id'] ?? 0;

            /** @var SalutationRepository $salutationRepo */
            $salutationRepo = $this->em->getRepository(Salutation::class);

            $salutation = $salutationRepo->getOne($currentSpace, $this->grantService->getCurrentUserEntityGrants(Salutation::class), $salutationId);

            if ($salutation === null) {
                throw new SalutationNotFoundException();
            }

            $gender = $params['gender'] ? (int)$params['gender'] : 0;
            $birthday = new \DateTime($params['birthday']);

            $space = $entity->getOwner() ? $entity->getOwner()->getSpace() : null;

            $resident = new Resident();
            $resident->setFirstName($entity->getFirstName());
            $resident->setLastName($entity->getLastName());
            $resident->setMiddleName('');
            $resident->setSpace($space);
            $resident->setSalutation($salutation);
            $resident->setGender($gender);
            $resident->setSsn(null);
            $resident->setBirthday($birthday);
            $resident->setPhones([]);

            $this->validate($resident, null, ['api_admin_resident_add']);

            $this->em->persist($resident);

            $admissionType = isset($params['admission_type']) ? (int)$params['admission_type'] : 0;

            $admission = new ResidentAdmission();
            $admission->setResident($resident);
            $admission->setGroupType(GroupType::TYPE_FACILITY);
            $admission->setAdmissionType($admissionType);
            $admission->setNotes('');

            $date = $params['date'];
            if (!empty($date)) {
                $date = new \DateTime($params['date']);

                $now = new \DateTime('now');
                $date->setTime($now->format('H'), $now->format('i'), $now->format('s'));

                $admission->setDate($date);
                $admission->setStart($date);
            } else {
                $admission->setDate(null);
                $admission->setStart(null);
            }

            $residentAdmissionService->saveAsFacility($admission, $params, $admissionType, null, true, null);

            $this->validate($admission, null, ['api_admin_facility_add']);
            $this->em->persist($admission);

            //update resident for mobile
            $resident->setUpdatedAt(new \DateTime('now'));
            $this->em->persist($resident);

            $this->em->flush();

            if ($user !== null) {
                $spaceName = '';
                if ($user->getSpace() !== null) {
                    $spaceName = $user->getSpace()->getName();
                }

                $subject = 'New Lead Resident - ' . $resident->getFirstName() . ' ' . $resident->getLastName();

                $body = $this->container->get('templating')->render('@api_email/lead-resident.html.twig', array(
                    'subject' => $subject,
                    'spaceName' => $spaceName,
                    'fullName' => $resident->getFirstName() . ' ' . $resident->getLastName(),
                    'id' => $resident->getId(),
                    'baseUrl' => $baseUrl
                ));

                $emails = [$user->getEmail()];
                $status = $this->mailer->sendLeadResidentNotification($emails, $subject, $body, $spaceName);

                $emailLog = new EmailLog();
                $emailLog->setSuccess($status);
                $emailLog->setSubject($subject);
                $emailLog->setSpace($spaceName);
                $emailLog->setEmails($emails);

                $this->em->persist($emailLog);
                $this->em->flush();
            }

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param array $params
     * @throws \Throwable
     */
    public function spam(array $params): void
    {
        try {

            $this->em->getConnection()->beginTransaction();

            /** @var LeadRepository $repo */
            $repo = $this->em->getRepository(Lead::class);

            $ids = !empty($params['ids']) ? $params['ids'] : [];

            $leads = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Lead::class), $ids);

            if (empty($leads)) {
                throw new LeadNotFoundException();
            }

            $spam = !empty($params['spam']) ? (bool)$params['spam'] : false;

            /** @var Lead $lead */
            foreach ($leads as $lead) {
                if ($lead->isSpam() !== $spam) {
                    $lead->setSpamUpdated(new \DateTime('now'));
                }

                $lead->setSpam($spam);

                $this->em->persist($lead);
            }

            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }

    /**
     * @param User $user
     * @param $expand
     * @throws \Exception
     */
    public function saveLeadSectionExpandState(User $user, $expand): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            if ($user === null) {
                throw new UserNotFoundException();
            }

            $user->setLeadSectionExpand((bool)$expand);

            $this->em->persist($user);

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
     * @return Referral|null
     */
    private function saveReferral(Lead $lead, array $newReferral): ?Referral
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

        return $referral;
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
        $funnelStage = $funnelStageRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FunnelStage::class), $funnelStageId);

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
        $temperature = $temperatureRepo->getOne($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Temperature::class), $temperatureId);

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
     * @param Contact $contact
     */
    private function createLeadReferralContactActivity(Lead $lead, Contact $contact)
    {
        $activityTypeName = 'Referral';
        /** @var ActivityTypeRepository $typeRepo */
        $typeRepo = $this->em->getRepository(ActivityType::class);

        /** @var ActivityType $type */
        $type = $typeRepo->getByTitle($this->grantService->getCurrentSpace(), $activityTypeName);

        if ($type === null) {
            throw new ActivityTypeNotFoundException();
        }

        $activity = new Activity();
        $activity->setContact($contact);
        $activity->setType($type);
        $activity->setOwnerType(ActivityOwnerType::TYPE_CONTACT);
        $activity->setDate(new \DateTime('now'));
        $activity->setStatus($type->getDefaultStatus());
        $activity->setTitle($type->getTitle());
        $activity->setNotes($type->getTitle());
        $activity->setAssignTo(null);
        $activity->setDueDate(null);
        $activity->setReminderDate(null);
        $activity->setFacility(null);
        $activity->setLead(null);
        $activity->setReferral(null);
        $activity->setOrganization(null);
        $activity->setOutreach(null);

        if ($this->grantService->getCurrentSpace() === null) {
            $activity->setCreatedBy($lead->getOwner());
            $activity->setUpdatedBy($lead->getOwner());
        }

        $this->validate($activity, null, ['api_lead_contact_activity_add']);

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
     * @param Referral|null $referral
     * @return ChangeLog
     */
    private function leadAddChangeLog(Lead $lead, Referral $referral = null): ChangeLog
    {
        $name = $lead->getFirstName() . ' ' . $lead->getLastName();
        $id = $lead->getId();
        $ownerName = $lead->getOwner() ? ucfirst($lead->getOwner()->getFullName()) : '';
        $userName = $lead->getUpdatedBy() !== null ? ucfirst($lead->getUpdatedBy()->getFullName()) : '';
        $primaryFacility = $lead->getPrimaryFacility() ? $lead->getPrimaryFacility()->getName() : '';
        $date = new \DateTime('now');

        $sourceType = '';
        $source = '';
        $contact = '';
        if ($referral !== null && $referral->getType() !== null) {
            $sourceType = $referral->getType()->getTitle();

            if ($referral->getOrganization() !== null && $referral->getType()->isOrganizationRequired()) {
                $source = $referral->getOrganization()->getName();
            }

            if ($referral->getContact() !== null && $referral->getType()->isRepresentativeRequired()) {
                $contact = $referral->getContact()->getFirstName() . ' ' . $referral->getContact()->getLastName();
            }
        }

        $content = [
            'lead_name' => $name,
            'lead_id' => $id,
            'owner' => $ownerName,
            'primary_facility' => $primaryFacility,
            'user_name' => $userName,
            'created_at' => $date->format('m/d/Y H:i'),
            'source_type' => $sourceType,
            'source' => $source,
            'contact' => $contact
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

            $subject = 'Leads System User Activity - ' . $changeLog->getCreatedAt()->format('m/d/Y');

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
     * @param Referral|null $referral
     */
    private function leadStateEditChangeLog($oldState, $newState, Lead $lead, Referral $referral = null)
    {
        $name = $lead->getFirstName() . ' ' . $lead->getLastName();
        $id = $lead->getId();
        $ownerName = $lead->getOwner() ? ucfirst($lead->getOwner()->getFullName()) : '';
        $userName = $lead->getUpdatedBy() !== null ? ucfirst($lead->getUpdatedBy()->getFullName()) : '';
        $primaryFacility = $lead->getPrimaryFacility() ? $lead->getPrimaryFacility()->getName() : '';

        $oldState = State::getTypes()[$oldState];
        $newState = State::getTypes()[$newState];
        $date = new \DateTime('now');

        $sourceType = '';
        $source = '';
        $contact = '';
        if ($referral !== null && $referral->getType() !== null) {
            $sourceType = $referral->getType()->getTitle();

            if ($referral->getOrganization() !== null && $referral->getType()->isOrganizationRequired()) {
                $source = $referral->getOrganization()->getName();
            }

            if ($referral->getContact() !== null && $referral->getType()->isRepresentativeRequired()) {
                $contact = $referral->getContact()->getFirstName() . ' ' . $referral->getContact()->getLastName();
            }
        }

        $content = [
            'lead_name' => $name,
            'lead_id' => $id,
            'owner' => $ownerName,
            'primary_facility' => $primaryFacility,
            'old_state' => $oldState,
            'new_state' => $newState,
            'user_name' => $userName,
            'created_at' => $date->format('m/d/Y H:i'),
            'source_type' => $sourceType,
            'source' => $source,
            'contact' => $contact
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
