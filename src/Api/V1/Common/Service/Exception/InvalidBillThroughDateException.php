<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidBillThroughDateException extends ApiException
{
    /**
     * InvalidBillThroughDateException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_BILL_THROUGH_DATE_EXCEPTION]['message'], ResponseCode::INVALID_BILL_THROUGH_DATE_EXCEPTION);
    }
}