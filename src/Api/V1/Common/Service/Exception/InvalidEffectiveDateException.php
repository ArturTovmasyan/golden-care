<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidEffectiveDateException extends ApiException
{
    /**
     * InvalidEffectiveDateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_EFFECTIVE_DATE_EXCEPTION]['message'], ResponseCode::INVALID_EFFECTIVE_DATE_EXCEPTION);
    }
}