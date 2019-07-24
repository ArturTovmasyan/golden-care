<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class IncorrectChangeLogException extends ApiException
{
    /**
     * IncorrectChangeLogException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INCORRECT_CHANGE_LOG_TYPE_EXCEPTION]['message'], ResponseCode::INCORRECT_CHANGE_LOG_TYPE_EXCEPTION);
    }
}