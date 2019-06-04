<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentAdmissionOnlyReadmitException extends \RuntimeException
{
    /**
     * ResidentAdmissionOnlyReadmitException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_ADMISSION_ONLY_READMIT_EXCEPTION);
    }
}