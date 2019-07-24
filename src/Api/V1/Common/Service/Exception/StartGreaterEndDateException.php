<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class StartGreaterEndDateException extends ApiException
{
    /**
     * StartGreaterEndDateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::START_GREATER_END_DATE_EXCEPTION]['message'], ResponseCode::START_GREATER_END_DATE_EXCEPTION);
    }
}