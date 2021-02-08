<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidDischargeDateException extends ApiException
{
    /**
     * InvalidDischargeDateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_DISCHARGE_DATE_EXCEPTION]['message'], ResponseCode::INVALID_DISCHARGE_DATE_EXCEPTION);
    }
}