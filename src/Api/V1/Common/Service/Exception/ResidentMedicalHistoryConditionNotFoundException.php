<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentMedicalHistoryConditionNotFoundException extends \RuntimeException
{
    /**
     * ResidentMedicalHistoryConditionNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION);
    }
}