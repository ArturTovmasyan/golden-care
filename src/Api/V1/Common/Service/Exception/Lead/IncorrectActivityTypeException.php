<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class IncorrectActivityTypeException extends ApiException
{
    /**
     * IncorrectActivityTypeException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_INCORRECT_ACTIVITY_TYPE_EXCEPTION]['message'], ResponseCode::LEAD_INCORRECT_ACTIVITY_TYPE_EXCEPTION);
    }
}