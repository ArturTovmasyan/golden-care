<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserNotFoundException extends \RuntimeException
{
    /**
     * SpaceNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::USER_NOT_FOUND_EXCEPTION);
    }
}