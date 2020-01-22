<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class BaseRateNotBeBlankException extends ApiException
{
    /**
     * BaseRateNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::BASE_RATE_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::BASE_RATE_NOT_BE_BLANK_EXCEPTION);
    }
}