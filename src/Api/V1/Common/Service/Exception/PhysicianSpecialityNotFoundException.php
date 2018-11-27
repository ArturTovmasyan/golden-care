<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PhysicianSpecialityNotFoundException extends \RuntimeException
{
    /**
     * PhysicianSpecialityNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_PHYSICIAN_SPECIALITY_EXCEPTION_NOT_FOUND);
    }
}