<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class LastResidentAdmissionNotFoundException extends \RuntimeException
{
    /**
     * LastResidentAdmissionNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LAST_RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION]['message'], ResponseCode::LAST_RESIDENT_ADMISSION_NOT_FOUND_EXCEPTION);
    }
}