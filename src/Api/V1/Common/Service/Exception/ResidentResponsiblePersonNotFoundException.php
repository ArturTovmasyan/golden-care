<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentResponsiblePersonNotFoundException extends \RuntimeException
{
    /**
     * ResidentResponsiblePersonNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION);
    }
}