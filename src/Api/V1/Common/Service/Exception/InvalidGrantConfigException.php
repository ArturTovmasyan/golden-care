<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidGrantConfigException extends ApiException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_GRANT_CONFIG]['message'], ResponseCode::INVALID_GRANT_CONFIG);
    }
}