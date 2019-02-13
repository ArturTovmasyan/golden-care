<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DefaultRoleNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::DEFAULT_ROLE_NOT_FOUND_EXCEPTION);
    }
}