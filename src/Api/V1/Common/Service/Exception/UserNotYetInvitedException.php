<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserNotYetInvitedException extends \RuntimeException
{
    /**
     * UserNotYetInvitedException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::USER_NOT_YET_INVITED_EXCEPTION);
    }
}
