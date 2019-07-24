<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SpecialityNotFoundException extends ApiException
{
    /**
     * SpecialityNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::SPECIALITY_NOT_FOUND_EXCEPTION]['message'], ResponseCode::SPECIALITY_NOT_FOUND_EXCEPTION);
    }
}