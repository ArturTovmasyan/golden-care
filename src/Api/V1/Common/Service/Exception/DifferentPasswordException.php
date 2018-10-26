<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DifferentPasswordException extends \RuntimeException
{
    /**
     * DifferentPasswordException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::NEW_PASSWORD_MUST_BE_DIFFERENT_EXCEPTION);
    }
}