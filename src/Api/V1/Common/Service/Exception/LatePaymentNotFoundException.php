<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class LatePaymentNotFoundException extends ApiException
{
    /**
     * LatePaymentNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LATE_PAYMENT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::LATE_PAYMENT_NOT_FOUND_EXCEPTION);
    }
}