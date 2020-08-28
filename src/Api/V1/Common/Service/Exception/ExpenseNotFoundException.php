<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ExpenseNotFoundException extends ApiException
{
    /**
     * ExpenseNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::EXPENSE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::EXPENSE_NOT_FOUND_EXCEPTION);
    }
}