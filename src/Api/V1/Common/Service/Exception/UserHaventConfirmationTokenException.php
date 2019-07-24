<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserHaventConfirmationTokenException extends ApiException
{
    /**
     * UserHaventConfirmationTokenException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_USER_CONFIRMATION_TOKEN]['message'], ResponseCode::INVALID_USER_CONFIRMATION_TOKEN);
    }
}