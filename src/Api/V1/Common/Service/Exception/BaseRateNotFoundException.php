<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class BaseRateNotFoundException extends ApiException
{
    /**
     * BaseRateNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::BASE_RATE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::BASE_RATE_NOT_FOUND_EXCEPTION);
    }
}