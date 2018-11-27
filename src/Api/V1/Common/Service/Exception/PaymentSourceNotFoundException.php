<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PaymentSourceNotFoundException extends \RuntimeException
{
    /**
     * PaymentSourceNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::PAYMENT_SOURCE_NOT_FOUND_EXCEPTION);
    }
}