<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ExpenseItemNotFoundException extends ApiException
{
    /**
     * ExpenseItemNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::EXPENSE_ITEM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::EXPENSE_ITEM_NOT_FOUND_EXCEPTION);
    }
}