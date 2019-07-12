<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentAdmissionNotFoundException extends \RuntimeException
{
    /**
     * ResidentAdmissionNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION);
    }
}