<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentExpenseItemNotFoundException extends ApiException
{
    /**
     * ResidentExpenseItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_EXPENSE_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_EXPENSE_ITEM_NOT_FOUND_EXCEPTION);
    }
}