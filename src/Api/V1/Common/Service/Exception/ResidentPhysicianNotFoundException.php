<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentPhysicianNotFoundException extends \RuntimeException
{
    /**
     * ResidentPhysicianNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_PHYSICIAN_NOT_FOUND_EXCEPTION);
    }
}