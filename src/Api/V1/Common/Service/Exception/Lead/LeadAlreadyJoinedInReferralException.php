<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class LeadAlreadyJoinedInReferralException extends ApiException
{
    /**
     * LeadAlreadyJoinedInReferralException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_ALREADY_JOINED_IN_REFERRAL_EXCEPTION]['message'], ResponseCode::LEAD_ALREADY_JOINED_IN_REFERRAL_EXCEPTION);
    }
}