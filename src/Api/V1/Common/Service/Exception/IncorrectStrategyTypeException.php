<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class IncorrectStrategyTypeException extends ApiException
{
    /**
     * IncorrectStrategyTypeException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INCORRECT_STRATEGY_TYPE_EXCEPTION]['message'], ResponseCode::INCORRECT_STRATEGY_TYPE_EXCEPTION);
    }
}