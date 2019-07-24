<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class RoleNotFoundException extends ApiException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ROLE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ROLE_NOT_FOUND_EXCEPTION);
    }
}