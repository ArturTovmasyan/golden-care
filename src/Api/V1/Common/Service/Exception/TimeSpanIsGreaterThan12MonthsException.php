<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class TimeSpanIsGreaterThan12MonthsException extends \RuntimeException
{
    /**
     * TimeSpanIsGreaterThan12MonthsException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::TIME_SPAN_IS_GREATHER_THAN_12_MONTHS_EXCEPTION]['message'], ResponseCode::TIME_SPAN_IS_GREATHER_THAN_12_MONTHS_EXCEPTION);
    }
}