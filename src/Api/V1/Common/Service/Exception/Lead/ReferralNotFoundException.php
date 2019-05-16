<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class ReferralNotFoundException extends \RuntimeException
{
    /**
     * ReferralNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::LEAD_REFERRAL_NOT_FOUND_EXCEPTION);
    }
}