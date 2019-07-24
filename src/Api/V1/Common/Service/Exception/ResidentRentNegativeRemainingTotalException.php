<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentRentNegativeRemainingTotalException extends ApiException
{
    /**
     * ResidentRentNegativeRemainingTotalException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_RENT_NEGATIVE_REMAINING_TOTAL]['message'], ResponseCode::RESIDENT_RENT_NEGATIVE_REMAINING_TOTAL);
    }
}