<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class MedicalHistoryConditionNotSingleException extends \RuntimeException
{
    /**
     * MedicalHistoryConditionNotSingleException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::MEDICAL_HISTORY_CONDITION_NOT_SINGLE_EXCEPTION]['message'], ResponseCode::MEDICAL_HISTORY_CONDITION_NOT_SINGLE_EXCEPTION);
    }
}