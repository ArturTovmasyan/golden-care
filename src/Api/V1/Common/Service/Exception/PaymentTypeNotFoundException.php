<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PaymentTypeNotFoundException extends ApiException
{
    /**
     * PaymentTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::PAYMENT_TYPE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::PAYMENT_TYPE_NOT_FOUND_EXCEPTION);
    }
}