<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserAlreadyInvitedException extends ApiException
{
    /**
     * UserAlreadyInvitedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::USER_ALREADY_INVITED_EXCEPTION]['message'], ResponseCode::USER_ALREADY_INVITED_EXCEPTION);
    }
}
