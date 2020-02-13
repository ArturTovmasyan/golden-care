<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PaymentSourceDuplicateBaseRateByDateException extends ApiException
{
    /**
     * PaymentSourceDuplicateBaseRateByDateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::PAYMENT_SOURCE_DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION]['message'], ResponseCode::PAYMENT_SOURCE_DUPLICATE_BASE_RATE_BY_DATE_EXCEPTION);
    }
}