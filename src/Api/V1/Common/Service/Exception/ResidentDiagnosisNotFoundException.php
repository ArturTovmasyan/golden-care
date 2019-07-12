<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentDiagnosisNotFoundException extends \RuntimeException
{
    /**
     * ResidentDiagnosisNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_DIAGNOSIS_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_DIAGNOSIS_NOT_FOUND_EXCEPTION);
    }
}