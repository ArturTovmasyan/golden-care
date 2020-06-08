<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Lead\Activity;
use App\Entity\Lead\ContactPhone;
use App\Entity\Lead\Lead;
use App\Entity\Lead\Outreach;
use App\Entity\Lead\Referral;
use App\Model\Lead\State;
use App\Model\Phone;
use App\Model\Report\Lead\ActivityList;
use App\Model\Report\Lead\LeadList;
use App\Model\Report\Lead\OutreachList;
use App\Model\Report\Lead\ReferralList;
use App\Repository\Lead\ActivityRepository;
use App\Repository\Lead\ContactPhoneRepository;
use App\Repository\Lead\LeadRepository;
use App\Repository\Lead\OutreachRepository;
use App\Repository\Lead\ReferralRepository;

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
            $startDate = new \DateTime($dateFrom);
        } else {
            $startDate = $currentDate;
        }

        if (!empty($dateTo)) {
            $endDate = new \DateTime($dateTo);
        } else {
            $endDate = date_modify($currentDate, '+1 day');
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

                    $stringFacilities = implode("\r\n", $facilities);

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
     * @return ReferralList
     */
    public function getReferralReport($group, ?bool $groupAll, $groupIds, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId, $assessmentFormId): ReferralList
    {
        $currentSpace = $this->grantService->getCurrentSpace();

        $currentDate = new \DateTime('now');

        if (!empty($dateFrom)) {
            $startDate = new \DateTime($dateFrom);
        } else {
            $startDate = $currentDate;
        }

        if (!empty($dateTo)) {
            $endDate = new \DateTime($dateTo);
        } else {
            $endDate = date_modify($currentDate, '+1 day');
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
            $startDate = new \DateTime($dateFrom);
        } else {
            $startDate = $currentDate;
        }

        if (!empty($dateTo)) {
            $endDate = new \DateTime($dateTo);
        } else {
            $endDate = date_modify($currentDate, '+1 day');
        }

        /** @var ActivityRepository $repo */
        $repo = $this->em->getRepository(Activity::class);

        $activities = $repo->getActivityList($currentSpace, $this->grantService->getCurrentUserEntityGrants(Activity::class), $startDate, $endDate);

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
            $startDate = new \DateTime($dateFrom);
        } else {
            $startDate = $currentDate;
        }

        if (!empty($dateTo)) {
            $endDate = new \DateTime($dateTo);
        } else {
            $endDate = date_modify($currentDate, '+1 day');
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

        $a = $finalOutreaches;

        $report = new OutreachList();
        $report->setOutreaches($finalOutreaches);

        return $report;
    }
}