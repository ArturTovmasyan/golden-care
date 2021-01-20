<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class StartGreaterValidThroughDateException extends ApiException
{
    /**
     * StartGreaterValidThroughDateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::START_GREATER_VALID_THROUGH_DATE_EXCEPTION]['message'], ResponseCode::START_GREATER_VALID_THROUGH_DATE_EXCEPTION);
    }
}