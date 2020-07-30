<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentReadmitOnlyAfterDischargeException extends ApiException
{
    /**
     * ResidentReadmitOnlyAfterDischargeException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_READMIT_ONLY_AFTER_DISCHARGE_EXCEPTION]['message'], ResponseCode::RESIDENT_READMIT_ONLY_AFTER_DISCHARGE_EXCEPTION);
    }
}