<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DiscountItemNotFoundException extends ApiException
{
    /**
     * DiscountItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DISCOUNT_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::DISCOUNT_ITEM_NOT_FOUND_EXCEPTION);
    }
}