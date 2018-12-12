<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentPaymentNegativeRemainingTotalException extends \RuntimeException
{
    /**
     * ResidentPaymentNegativeRemainingTotalException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_PAYMENT_NEGATIVE_REMAINING_TOTAL);
    }
}