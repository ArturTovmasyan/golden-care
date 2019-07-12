<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SystemErrorException extends \RuntimeException
{
    /**
     * UserAlreadyJoinedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::SYSTEM_ERROR]['message'], ResponseCode::SYSTEM_ERROR);
    }
}