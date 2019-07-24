<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CareLevelNotFoundException extends ApiException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CARE_LEVEL_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CARE_LEVEL_NOT_FOUND_EXCEPTION);
    }
}