<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PhysicianNotFoundException extends ApiException
{
    /**
     * PhysicianNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::PHYSICIAN_NOT_FOUND_EXCEPTION]['message'], ResponseCode::PHYSICIAN_NOT_FOUND_EXCEPTION);
    }
}