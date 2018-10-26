<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UserAlreadyJoinedException extends \RuntimeException
{
    /**
     * UserAlreadyJoinedException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::USER_ALREADY_JOINED_EXCEPTION);
    }
}