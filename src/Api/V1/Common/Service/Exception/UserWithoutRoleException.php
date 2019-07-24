<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserWithoutRoleException extends ApiException
{
    /**
     * UserHaventConfirmationTokenException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::USER_WITHOUT_ROLE_EXCEPTION]['message'], ResponseCode::USER_WITHOUT_ROLE_EXCEPTION);
    }
}