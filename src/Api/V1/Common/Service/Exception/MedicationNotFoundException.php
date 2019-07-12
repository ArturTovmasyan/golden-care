<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class MedicationNotFoundException extends \RuntimeException
{
    /**
     * MedicationNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::MEDICATION_NOT_FOUND_EXCEPTION]['message'], ResponseCode::MEDICATION_NOT_FOUND_EXCEPTION);
    }
}