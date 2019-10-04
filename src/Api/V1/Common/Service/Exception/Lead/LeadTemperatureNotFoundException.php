<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class LeadTemperatureNotFoundException extends ApiException
{
    /**
     * LeadTemperatureNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_LEAD_TEMPERATURE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::LEAD_LEAD_TEMPERATURE_NOT_FOUND_EXCEPTION);
    }
}