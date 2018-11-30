<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentHavePrimaryPhysicianException extends \RuntimeException
{
    /**
     * ResidentHavePrimaryPhysicianException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_HAVE_PRIMARY_PHYSICIAN_EXCEPTION);
    }
}