<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentDiscountItemNotFoundException extends ApiException
{
    /**
     * ResidentDiscountItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_DISCOUNT_ITEM_NOT_FOUND_EXCEPTION);
    }
}