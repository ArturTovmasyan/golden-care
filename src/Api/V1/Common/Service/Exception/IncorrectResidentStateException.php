<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class IncorrectResidentStateException extends ApiException
{
    /**
     * IncorrectResidentStateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INCORRECT_RESIDENT_STATE_EXCEPTION]['message'], ResponseCode::INCORRECT_RESIDENT_STATE_EXCEPTION);
    }
}