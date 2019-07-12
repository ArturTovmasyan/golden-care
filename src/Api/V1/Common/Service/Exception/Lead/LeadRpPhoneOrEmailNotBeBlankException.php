<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class LeadRpPhoneOrEmailNotBeBlankException extends \RuntimeException
{
    /**
     * LeadRpPhoneOrEmailNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_RP_PHONE_OR_EMAIL_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::LEAD_RP_PHONE_OR_EMAIL_NOT_BE_BLANK_EXCEPTION);
    }
}