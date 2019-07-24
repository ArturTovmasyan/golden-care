<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DiagnosisNotSingleException extends ApiException
{
    /**
     * DiagnosisNotSingleException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DIAGNOSIS_NOT_SINGLE_EXCEPTION]['message'], ResponseCode::DIAGNOSIS_NOT_SINGLE_EXCEPTION);
    }
}