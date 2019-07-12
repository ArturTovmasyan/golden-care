<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UnhandledRentPeriodException extends \RuntimeException
{
    /**
     * UnhandledRentPeriodException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::UNHANDLED_RENT_PERIOD_EXCEPTION]['message'], ResponseCode::UNHANDLED_RENT_PERIOD_EXCEPTION);
    }
}