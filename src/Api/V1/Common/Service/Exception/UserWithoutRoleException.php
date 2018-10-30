<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserWithoutRoleException extends \RuntimeException
{
    /**
     * UserHaventConfirmationTokenException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::USER_WITHOUT_ROLE_EXCEPTION);
    }
}