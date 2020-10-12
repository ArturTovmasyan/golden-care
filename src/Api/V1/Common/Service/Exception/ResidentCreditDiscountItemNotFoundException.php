<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentCreditDiscountItemNotFoundException extends ApiException
{
    /**
     * ResidentCreditDiscountItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_CREADIT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_CREADIT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION);
    }
}