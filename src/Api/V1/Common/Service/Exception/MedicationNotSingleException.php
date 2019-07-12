<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class MedicationNotSingleException extends \RuntimeException
{
    /**
     * MedicationNotSingleException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::MEDICATION_NOT_SINGLE_EXCEPTION]['message'], ResponseCode::MEDICATION_NOT_SINGLE_EXCEPTION);
    }
}