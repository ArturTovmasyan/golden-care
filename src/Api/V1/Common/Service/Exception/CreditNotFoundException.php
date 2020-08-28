<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CreditNotFoundException extends ApiException
{
    /**
     * CreditNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CREDIT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CREDIT_NOT_FOUND_EXCEPTION);
    }
}