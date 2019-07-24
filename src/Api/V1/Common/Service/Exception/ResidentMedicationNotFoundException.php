<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentMedicationNotFoundException extends ApiException
{
    /**
     * ResidentMedicationNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_MEDICATION_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_MEDICATION_NOT_FOUND_EXCEPTION);
    }
}