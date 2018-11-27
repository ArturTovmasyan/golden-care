<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DuplicateSpecialityRequestException extends \RuntimeException
{
    /**
     * DuplicateSpecialityRequestException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::PHYSICIAN_SPECIALITY_DUPLICATE_REQUEST_EXCEPTION);
    }
}