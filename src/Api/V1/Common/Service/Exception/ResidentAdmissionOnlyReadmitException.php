<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentAdmissionOnlyReadmitException extends ApiException
{
    /**
     * ResidentAdmissionOnlyReadmitException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_ADMISSION_ONLY_READMIT_EXCEPTION]['message'], ResponseCode::RESIDENT_ADMISSION_ONLY_READMIT_EXCEPTION);
    }
}