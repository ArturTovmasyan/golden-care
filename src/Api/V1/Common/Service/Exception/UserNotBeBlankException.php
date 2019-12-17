<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserNotBeBlankException extends ApiException
{
    /**
     * UserNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::USER_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::USER_NOT_BE_BLANK_EXCEPTION);
    }
}