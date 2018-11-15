<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DiagnosisNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::DIAGNOSIS_NOT_FOUND_EXCEPTION);
    }
}