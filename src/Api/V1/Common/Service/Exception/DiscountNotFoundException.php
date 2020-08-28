<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DiscountNotFoundException extends ApiException
{
    /**
     * DiscountNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DISCOUNT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::DISCOUNT_NOT_FOUND_EXCEPTION);
    }
}