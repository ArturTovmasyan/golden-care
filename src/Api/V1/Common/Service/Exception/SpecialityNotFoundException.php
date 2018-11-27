<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SpecialityNotFoundException extends \RuntimeException
{
    /**
     * SpecialityNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::SPECIALITY_NOT_FOUND_EXCEPTION);
    }
}