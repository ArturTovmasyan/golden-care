<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class StartAndEndDateNotSameMonthException extends ApiException
{
    /**
     * StartAndEndDateNotSameMonthException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::START_AND_END_DATE_NOT_SAME_MONTH_EXCEPTION]['message'], ResponseCode::START_AND_END_DATE_NOT_SAME_MONTH_EXCEPTION);
    }
}