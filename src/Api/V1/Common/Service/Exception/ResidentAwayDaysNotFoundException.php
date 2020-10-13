<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentAwayDaysNotFoundException extends ApiException
{
    /**
     * ResidentAwayDaysNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_AWAY_DAYS_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_AWAY_DAYS_NOT_FOUND_EXCEPTION);
    }
}