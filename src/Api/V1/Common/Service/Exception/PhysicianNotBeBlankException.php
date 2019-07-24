<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PhysicianNotBeBlankException extends ApiException
{
    /**
     * PhysicianNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::PHYSICIAN_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::PHYSICIAN_NOT_BE_BLANK_EXCEPTION);
    }
}