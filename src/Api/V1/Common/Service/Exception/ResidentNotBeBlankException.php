<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentNotBeBlankException extends ApiException
{
    /**
     * ResidentNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::RESIDENT_NOT_BE_BLANK_EXCEPTION);
    }
}