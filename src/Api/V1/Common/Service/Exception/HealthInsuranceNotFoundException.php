<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class HealthInsuranceNotFoundException extends \RuntimeException
{
    /**
     * HealthInsuranceNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::HEALTH_INSURANCE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::HEALTH_INSURANCE_NOT_FOUND_EXCEPTION);
    }
}