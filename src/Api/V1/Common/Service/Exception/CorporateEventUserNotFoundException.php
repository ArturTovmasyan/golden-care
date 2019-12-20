<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CorporateEventUserNotFoundException extends ApiException
{
    /**
     * CorporateEventUserNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CORPORATE_EVENT_USER_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CORPORATE_EVENT_USER_NOT_FOUND_EXCEPTION);
    }
}