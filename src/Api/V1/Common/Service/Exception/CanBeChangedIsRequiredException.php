<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CanBeChangedIsRequiredException extends ApiException
{
    /**
     * CanBeChangedIsRequiredException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CAN_BE_CHANGED_IS_REQUIRED_EXCEPTION]['message'], ResponseCode::CAN_BE_CHANGED_IS_REQUIRED_EXCEPTION);
    }
}