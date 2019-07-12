<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class MedicalHistoryConditionNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION]['message'], ResponseCode::MEDICAL_HISTORY_CONDITION_NOT_FOUND_EXCEPTION);
    }
}