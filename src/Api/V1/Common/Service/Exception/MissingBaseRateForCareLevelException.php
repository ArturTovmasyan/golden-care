<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class MissingBaseRateForCareLevelException extends ApiException
{
    /**
     * MissingBaseRateForCareLevelException constructor.
     * @param $message
     */
    public function __construct($message)
    {
        parent::__construct($message, ResponseCode::MISSING_BASE_RATE_FOR_CARE_LEVEL);
    }
}