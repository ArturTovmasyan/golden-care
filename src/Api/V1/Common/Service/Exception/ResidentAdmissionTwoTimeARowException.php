<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentAdmissionTwoTimeARowException extends ApiException
{
    /**
     * ResidentAdmissionTwoTimeARowException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_ADMISSION_TWO_TIME_A_ROW_EXCEPTION]['message'], ResponseCode::RESIDENT_ADMISSION_TWO_TIME_A_ROW_EXCEPTION);
    }
}