<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserNotYetInvitedException extends ApiException
{
    /**
     * UserNotYetInvitedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::USER_NOT_YET_INVITED_EXCEPTION]['message'], ResponseCode::USER_NOT_YET_INVITED_EXCEPTION);
    }
}
