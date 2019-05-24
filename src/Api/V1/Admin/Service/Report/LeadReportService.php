<?php

namespace App\Api\V1\Admin\Service\Report;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Lead\Referral;
use App\Entity\Lead\ReferralPhone;
use App\Model\Phone;
use App\Model\Report\Lead\ReferralList;
use App\Repository\Lead\ReferralPhoneRepository;
use App\Repository\Lead\ReferralRepository;

class LeadReportService extends BaseService
{
    /**
     * @param $group
     * @param bool|null $groupAll
     * @param $groupId
     * @param bool|null $residentAll
     * @param $residentId
     * @param $date
     * @param $dateFrom
     * @param $dateTo
     * @param $assessmentId
     * @return ReferralList
     */
    public function getReferralReport($group, ?bool $groupAll, $groupId, ?bool $residentAll, $residentId, $date, $dateFrom, $dateTo, $assessmentId)
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
            $referralIds = array_map(function($item){return $item['id'];} , $referrals);
            $referralIds = array_unique($referralIds);

            /** @var ReferralPhoneRepository $referralPhoneRepo */
            $referralPhoneRepo = $this->em->getRepository(ReferralPhone::class);

            $referralPhones = $referralPhoneRepo->getByReferralIds($currentSpace, $this->grantService->getCurrentUserEntityGrants(ReferralPhone::class), $referralIds);

            foreach ($referrals as $referral) {
                if (!empty($referralPhones)) {
                    $finalPhones = [];
                    foreach ($referralPhones as $phone) {
                        if ($phone['rId'] === $referral['id']) {
                            $finalPhones[] = $phone['primary'] ? '(P)' . Phone::$typeNames[$phone['type']] . ' : ' .  $phone['number'] : Phone::$typeNames[$phone['type']] . ' : ' .  $phone['number'];
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
}