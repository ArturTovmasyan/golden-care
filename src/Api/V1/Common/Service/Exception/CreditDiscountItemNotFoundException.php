<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CreditDiscountItemNotFoundException extends ApiException
{
    /**
     * CreditDiscountItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CREADIT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CREADIT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION);
    }
}