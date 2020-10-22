<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class KeyFinanceDatesNotFoundException extends ApiException
{
    /**
     * KeyFinanceDatesNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::KEY_FINANCE_DATES_NOT_FOUND_EXCEPTION]['message'], ResponseCode::KEY_FINANCE_DATES_NOT_FOUND_EXCEPTION);
    }
}