<?php

namespace App\Model\Report\Lead;

use App\Model\Report\Base;

class ReferralList extends Base
{
    /**
     * @var array
     */
    private $referrals = [];

    /**
     * @param $referrals
     */
    public function setReferrals($referrals): void
    {
        $this->referrals = $referrals;
    }

    /**
     * @return array
     */
    public function getReferrals(): ?array
    {
        return $this->referrals;
    }
}

