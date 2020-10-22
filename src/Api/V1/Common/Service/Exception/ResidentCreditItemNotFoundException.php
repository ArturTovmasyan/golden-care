<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentCreditItemNotFoundException extends ApiException
{
    /**
     * ResidentCreditItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_CREADIT_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_CREADIT_ITEM_NOT_FOUND_EXCEPTION);
    }
}