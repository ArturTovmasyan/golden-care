<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserHaventConfirmationTokenException extends \RuntimeException
{
    /**
     * UserHaventConfirmationTokenException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::INVALID_USER_CONFIRMATION_TOKEN);
    }
}