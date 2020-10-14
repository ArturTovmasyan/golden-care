<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentKeyFinanceDateNotFoundException extends ApiException
{
    /**
     * ResidentKeyFinanceDateNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_KEY_FINANCE_DATE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_KEY_FINANCE_DATE_NOT_FOUND_EXCEPTION);
    }
}