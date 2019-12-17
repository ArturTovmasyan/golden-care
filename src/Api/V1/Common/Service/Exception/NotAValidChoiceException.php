<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class NotAValidChoiceException extends ApiException
{
    /**
     * NotAValidChoiceException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::NOT_A_VALID_CHOICE_EXCEPTION]['message'], ResponseCode::NOT_A_VALID_CHOICE_EXCEPTION);
    }
}