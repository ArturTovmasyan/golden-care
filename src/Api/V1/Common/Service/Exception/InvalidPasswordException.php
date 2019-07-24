<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidPasswordException extends ApiException
{
    /**
     * InvalidPasswordException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_PASSWORD_EXCEPTION]['message'], ResponseCode::INVALID_PASSWORD_EXCEPTION);
    }
}