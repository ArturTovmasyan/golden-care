<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Facility;
use App\Entity\Lead\Activity;
use App\Entity\Lead\Contact;
use App\Entity\Lead\ContactPhone;
use App\Entity\Lead\Lead;
use App\Entity\Lead\LeadFunnelStage;
use App\Entity\Lead\Outreach;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferrerType;
use App\Entity\Lead\WebEmail;
use App\Model\Lead\ActivityOwnerType;
use App\Model\Lead\State;
use App\Model\Phone;
use App\Model\Report\Lead\ActivityList;
use App\Model\Report\Lead\ContactList;
use App\Model\Report\Lead\LeadList;
use App\Model\Report\Lead\OutreachList;
use App\Model\Report\Lead\ReferralList;
use App\Model\Report\Lead\WebEmailList;
use App\Repository\FacilityRepository;
use App\Repository\Lead\ActivityRepository;
use App\Repository\Lead\ContactPhoneRepository;
use App\Repository\Lead\ContactRepository;
use App\Repository\Lead\LeadFunnelStageRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\OutreachRepository;
use App\Repository\Lead\ReferralRepository;
use App\Repository\Lead\ReferrerTypeRepository;
use App\Repository\Lead\WebEmailRepository;

class LeadReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return LeadList
     */
    public function getLeadReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): LeadList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        $typeIds = null;
        if (!empty($groupIds)) {
            $typeIds = !empty($groupIds[0]) ? $groupIds : [];
        }

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        $leads = $repo->getLeadList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $startDate, $endDate, $typeIds);

        $finalLeads = [];
        if (!empty($leads)) {
            foreach ($leads as $lead) {
                $finalLeads[$lead[0]['id']] = [
                    'id' => $lead[0]['id'],
                    'firstName' => $lead[0]['firstName'],
                    'lastName' => $lead[0]['lastName'],
                    'state' => State::getTypes()[$lead[0]['state']],
                    'rpFirstName' => $lead[0]['responsiblePersonFirstName'],
                    'rpLastName' => $lead[0]['responsiblePersonLastName'],
                    'rpAddress1' => $lead[0]['responsiblePersonAddress_1'],
                    'rpAddress2' => $lead[0]['responsiblePersonAddress_2'],
                    'rpPhone' => $lead[0]['responsiblePersonPhone'],
                    'rpEmail' => $lead[0]['responsiblePersonEmail'],
                    'notes' => $lead[0]['notes'],
                    'rpCity' => $lead['rpCity'],
                    'rpStateAbbr' => $lead['rpStateAbbr'],
                    'rpZipMain' => $lead['rpZipMain'],
                    'careType' => $lead['careType'],
                    'paymentType' => $lead['paymentType'],
                    'funnelStage' => $lead['funnelStage'],
                    'funnelDate' => $lead['funnelDate'],
                    'temperature' => $lead['temperature'],
                    'ownerFullName' => $lead['ownerFullName'],
                    'referralFullName' => $lead['referralFullName'],
                    'primaryFacility' => $lead['primaryFacility'],
                ];

                $facilities = [];
                if (!empty($lead[0]['facilities'])) {
                    $facilities = array_map(static function ($item) {
                        return $item['name'];
                    }, $lead[0]['facilities']);

                    $stringFacilities = implode(", ", $facilities);

                    $finalLeads[$lead[0]['id']]['secondaryFacilities'] = $stringFacilities;
                } else {
                    $finalLeads[$lead[0]['id']]['secondaryFacilities'] = $facilities;
                }
            }
        }

        $report = new LeadList();
        $report->setLeads($finalLeads);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return LeadList
     */
    public function getClosedLeadReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): LeadList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        $typeIds = null;
        if (!empty($groupIds)) {
            $typeIds = !empty($groupIds[0]) ? $groupIds : [];
        }

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var LeadFunnelStageRepository $repo */
        $repo = $this->em->getRepository(LeadFunnelStage::class);

        $leads = $repo->getClosedLeads($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $startDate, $endDate, $typeIds);

        $report = new LeadList();
        $report->setLeads($leads);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return LeadList
     */
    public function getSocialMediaLeadReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): LeadList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        $typeIds = null;
        if (!empty($groupIds)) {
            $typeIds = !empty($groupIds[0]) ? $groupIds : [];
        }

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('Y-m-01 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('Y-m-01 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('Y-m-t 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('Y-m-t 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var ReferrerTypeRepository $referrerTypeRepo */
        $referrerTypeRepo = $this->em->getRepository(ReferrerType::class);
        $referrerTypeTitles = ['Web Lead', 'Facebook Ad'];
        $referrerTypes = $referrerTypeRepo->findByTitles($currentSpace, $this->grantService->getCurrentUserEntityGrants(ReferrerType::class), $referrerTypeTitles);

        $referrerTypeIds = array_map(static function (ReferrerType $item) {
            return $item->getId();
        }, $referrerTypes);

        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);

        $leads = $repo->getSocialMediaLeadList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Lead::class), $startDate, $endDate, $referrerTypeIds, $typeIds);

        $report = new LeadList();
        $report->setLeads($leads);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ReferralList
     */
    public function getReferralReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ReferralList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var ReferralRepository $repo */
        $repo = $this->em->getRepository(Referral::class);

        $referrals = $repo->getReferralList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Referral::class), $startDate, $endDate);

        $finalReferrals = [];
        if (!empty($referrals)) {
            $contactIds = [];
            foreach ($referrals as $referral) {
                if ($referral['cId'] !== null) {
                    $contactIds[] = $referral['cId'];
                }
            }
            $contactIds = array_unique($contactIds);

            /** @var ContactPhoneRepository $contactPhoneRepo */
            $contactPhoneRepo = $this->em->getRepository(ContactPhone::class);

            $contactPhones = $contactPhoneRepo->getByContactIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ContactPhone::class), $contactIds);

            foreach ($referrals as $referral) {
                if (!empty($contactPhones)) {
                    $finalPhones = [];
                    foreach ($contactPhones as $phone) {
                        if ($phone['cId'] === $referral['cId']) {
                            $finalPhones[] = $phone['primary'] ? '(P)' . Phone::$typeNames[$phone['type']] . ' : ' . $phone['number'] : Phone::$typeNames[$phone['type']] . ' : ' . $phone['number'];
                        }
                    }

                    $stringPhones = implode("\r\n", $finalPhones);
                    $referral['phones'] = !empty($stringPhones) ? $stringPhones : 'N/A';
                }

                $emails = $referral['emails'];

                if (!empty($emails) && !empty($emails[0])) {

                    $stringEmails = implode("\r\n", $emails);
                    $referral['emails'] = $stringEmails;
                } else {
                    $referral['emails'] = 'N/A';
                }

                $finalReferrals[] = $referral;
            }
        }

        $report = new ReferralList();
        $report->setReferrals($finalReferrals);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ActivityList
     */
    public function getActivityReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ActivityList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        $activities = $repo->getActivityList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Activity::class), $startDate, $endDate, null);

        $report = new ActivityList();
        $report->setActivities($activities);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return OutreachList
     */
    public function getOutreachReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): OutreachList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var OutreachRepository $repo */
        $repo = $this->em->getRepository(Outreach::class);

        $outreaches = $repo->getOutreachList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Outreach::class), $startDate, $endDate);

        $finalOutreaches = [];
        if (!empty($outreaches)) {
            foreach ($outreaches as $outreach) {
                $finalOutreaches[$outreach[0]['id']] = [
                    'id' => $outreach[0]['id'],
                    'date' => $outreach[0]['date'],
                    'notes' => $outreach[0]['notes'],
                    'typeTitle' => $outreach['typeTitle'],
                    'organization' => $outreach['organizationName'],
                ];

                $contacts = [];
                if (!empty($outreach[0]['contacts'])) {
                    $contacts = array_map(static function ($item) {
                        return $item['firstName'] . ' ' . $item['lastName'];
                    }, $outreach[0]['contacts']);

                    $stringContacts = implode("\r\n", $contacts);

                    $finalOutreaches[$outreach[0]['id']]['contacts'] = $stringContacts;
                } else {
                    $finalOutreaches[$outreach[0]['id']]['contacts'] = $contacts;
                }

                $participants = [];
                if (!empty($outreach[0]['participants'])) {
                    $participants = array_map(static function ($item) {
                        return $item['firstName'] . ' ' . $item['lastName'];
                    }, $outreach[0]['participants']);

                    $stringParticipants = implode("\r\n", $participants);

                    $finalOutreaches[$outreach[0]['id']]['participants'] = $stringParticipants;
                } else {
                    $finalOutreaches[$outreach[0]['id']]['participants'] = $participants;
                }
            }
        }
        
        $report = new OutreachList();
        $report->setOutreaches($finalOutreaches);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ActivityList
     */
    public function getOutreachAndContactActivityReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ActivityList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        $activities = $repo->getActivityList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Activity::class), $startDate, $endDate, [ActivityOwnerType::TYPE_OUTREACH, ActivityOwnerType::TYPE_CONTACT, ActivityOwnerType::TYPE_REFERRAL, ActivityOwnerType::TYPE_ORGANIZATION]);

        /** @var FacilityRepository $facilityRepo */
        $facilityRepo = $this->em->getRepository(Facility::class);

        $facilities = $facilityRepo->getAll($currentSpace, null);
        $facilities = array_column($facilities, 'name', 'id');

        $finalActivities = [];
        if (!empty($activities)) {
            foreach ($activities as $activity) {
                if ($activity['facility'] === null) {
                    if ($activity['facilityIds'] !== null) {
                        $facilityIds = explode(',', $activity['facilityIds']);
                        $array_version = [];
                        foreach ($facilityIds as $facilityId) {
                            $array_version[] = $facilities[$facilityId];
                        }

                        $string_version = implode("\r\n", $array_version);
                        $activity['facility'] = $string_version;
                    } else {
                        $string_version = implode("\r\n", $facilities);
                        $activity['facility'] = $string_version;
                    }
                }

                $finalActivities[] = $activity;
            }
        }
        
        $report = new ActivityList();
        $report->setActivities($finalActivities);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return ContactList
     */
    public function getContactReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ContactList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var ContactRepository $repo */
        $repo = $this->em->getRepository(Contact::class);

        $contacts = $repo->getContactList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Contact::class), $startDate, $endDate);

        $finalContacts = [];
        if (!empty($contacts)) {
            $contactIds = array_map(static function ($item) {
                return $item['id'];
            }, $contacts);

            /** @var ContactPhoneRepository $contactPhoneRepo */
            $contactPhoneRepo = $this->em->getRepository(ContactPhone::class);

            $contactPhones = $contactPhoneRepo->getByContactIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ContactPhone::class), $contactIds);

            foreach ($contacts as $contact) {
                if (!empty($contactPhones)) {
                    $finalPhones = [];
                    foreach ($contactPhones as $phone) {
                        if ($phone['cId'] === $contact['id']) {
                            $finalPhones[] = $phone['primary'] ? '(P)' . Phone::$typeNames[$phone['type']] . ' : ' . $phone['number'] : Phone::$typeNames[$phone['type']] . ' : ' . $phone['number'];
                        }
                    }

                    $stringPhones = implode("\r\n", $finalPhones);
                    $contact['phones'] = !empty($stringPhones) ? $stringPhones : 'N/A';
                }

                $emails = $contact['emails'];

                if (!empty($emails) && !empty($emails[0])) {
                    $stringEmails = implode("\r\n", $emails);
                    $contact['emails'] = $stringEmails;
                } else {
                    $contact['emails'] = 'N/A';
                }

                $finalContacts[] = $contact;
            }
        }

        $report = new ContactList();
        $report->setContacts($finalContacts);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return WebEmailList
     */
    public function getWebEmailReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): WebEmailList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/d/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/d/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        /** @var WebEmailRepository $repo */
        $repo = $this->em->getRepository(WebEmail::class);

        $webEmails = $repo->getNotSpamWebEmailList($currentSpace, $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $startDate, $endDate);

        $report = new WebEmailList();
        $report->setWebEmails($webEmails);

        return $report;
    }

    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupIds
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @param $assessmentFormId
     * @return WebEmailList
     */
    public function getWebEmailPerMonthByFacilityReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): WebEmailList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        $typeIds = null;
        if (!empty($groupIds)) {
            $typeIds = !empty($groupIds[0]) ? $groupIds : [];
        }

        if (!empty($dateFrom)) {
            $start = new \DateTime($dateFrom);
            $startFormatted = $start->format('m/1/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        } else {
            $startFormatted = $currentDate->format('m/1/Y 00:00:00');
            $startDate = new \DateTime($startFormatted);
        }

        if (!empty($dateTo)) {
            $end = new \DateTime($dateTo);
            $endFormatted = $end->format('m/t/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        } else {
            $cloneCurrentDate = clone $currentDate;
            $endFormatted = $cloneCurrentDate->format('m/t/Y 23:59:59');
            $endDate = new \DateTime($endFormatted);
        }

        $dateToClone = clone $endDate;

        $subIntervals = [];
        while ($dateToClone >= $startDate) {
            $start = new \DateTime($dateToClone->format('Y-m-01 00:00:00'));
            $end = new \DateTime($dateToClone->format('Y-m-t 23:59:59'));

            $subIntervals[] = [
                'dateFrom' => $start,
                'dateTo' => $end
            ];

            $dateToClone->modify('last day of previous month');
        }

        $subIntervals = array_reverse($subIntervals);

        /** @var FacilityRepository $facilityRepo */
        $facilityRepo = $this->em->getRepository(Facility::class);

        $finalTypes = [];
        $types = $facilityRepo->orderedFindAll($currentSpace, $this->grantService->getCurrentUserEntityGrants(Facility::class));
        if (!empty($typeIds)) {
            /** @var Facility $type */
            foreach ($types as $type) {
                if (in_array($type->getId(), $typeIds, false)) {
                    $finalTypes[] = $type;
                }
            }
        } else {
            $finalTypes = $types;
        }

        /** @var WebEmailRepository $repo */
        $repo = $this->em->getRepository(WebEmail::class);

        $webEmails = $repo->getWebEmailListByIntervalAndFacility($currentSpace, $this->grantService->getCurrentUserEntityGrants(WebEmail::class), $startDate, $endDate, $typeIds);

        $data = [];
        foreach ($subIntervals  as $subInterval) {
            /** @var Facility $type */
            foreach ($finalTypes as $type) {
                $count = 0;
                foreach ($webEmails as $webEmail) {
                    $i = 0;
                    if ($webEmail['date'] >= $subInterval['dateFrom'] && $webEmail['date'] <= $subInterval['dateTo'] && $webEmail['typeId'] === $type->getId()) {
                        $i++;

                        $count += $i;
                    }
                }

                $data[] = [
                    'date' => $subInterval['dateFrom']->format('m/Y'),
                    'type' => $type->getName(),
                    'count' => $count,
                ];
            }
        }

        $report = new WebEmailList();
        $report->setWebEmails($data);

        return $report;
    }
}