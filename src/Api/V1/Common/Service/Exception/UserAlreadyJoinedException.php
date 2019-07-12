<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserAlreadyJoinedException extends \RuntimeException
{
    /**
     * UserAlreadyJoinedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::USER_ALREADY_JOINED_EXCEPTION]['message'], ResponseCode::USER_ALREADY_JOINED_EXCEPTION);
    }
}
