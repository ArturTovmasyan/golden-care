<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FacilityEventNotFoundException extends ApiException
{
    /**
     * ResidentEventNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FACILITY_EVENT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::FACILITY_EVENT_NOT_FOUND_EXCEPTION);
    }
}