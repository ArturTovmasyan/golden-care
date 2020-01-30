<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class RentReasonNotFoundException extends ApiException
{
    /**
     * RentReasonNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RENT_REASON_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RENT_REASON_NOT_FOUND_EXCEPTION);
    }
}