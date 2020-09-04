<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class RpPaymentTypeNotFoundException extends ApiException
{
    /**
     * RpPaymentTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RP_PAYMENT_TYPE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RP_PAYMENT_TYPE_NOT_FOUND_EXCEPTION);
    }
}