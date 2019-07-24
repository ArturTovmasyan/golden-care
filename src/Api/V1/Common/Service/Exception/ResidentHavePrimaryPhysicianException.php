<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentHavePrimaryPhysicianException extends ApiException
{
    /**
     * ResidentHavePrimaryPhysicianException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_HAVE_PRIMARY_PHYSICIAN_EXCEPTION]['message'], ResponseCode::RESIDENT_HAVE_PRIMARY_PHYSICIAN_EXCEPTION);
    }
}