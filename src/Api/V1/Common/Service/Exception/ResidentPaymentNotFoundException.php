<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentPaymentNotFoundException extends \RuntimeException
{
    /**
     * ResidentPaymentNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_PAYMENT_NOT_FOUND_EXCEPTION);
    }
}