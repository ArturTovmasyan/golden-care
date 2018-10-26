<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidPasswordException extends \RuntimeException
{
    /**
     * InvalidPasswordException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::INVALID_PASSWORD_EXCEPTION);
    }
}