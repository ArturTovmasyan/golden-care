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
        parent::__construct('', ResponseCode::PHYSICIAN_SPECIALITY_NOT_FOUND_EXCEPTION);
    }
}