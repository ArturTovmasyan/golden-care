<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentPaymentReceivedItemNotFoundException extends ApiException
{
    /**
     * ResidentPaymentReceivedItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_PAYMENT_RECEIVED_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_PAYMENT_RECEIVED_ITEM_NOT_FOUND_EXCEPTION);
    }
}