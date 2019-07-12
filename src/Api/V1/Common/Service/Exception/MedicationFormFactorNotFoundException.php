<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class MedicationFormFactorNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::MEDICATION_FORM_FACTOR_NOT_FOUND_EXCEPTION]['message'], ResponseCode::MEDICATION_FORM_FACTOR_NOT_FOUND_EXCEPTION);
    }
}