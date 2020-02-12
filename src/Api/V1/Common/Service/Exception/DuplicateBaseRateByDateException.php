<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DuplicateBaseRateByDateException extends ApiException
{
    /**
     * DuplicateBaseRateByDateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION]['message'], ResponseCode::DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION);
    }
}