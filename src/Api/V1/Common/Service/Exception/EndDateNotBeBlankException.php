<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class EndDateNotBeBlankException extends \RuntimeException
{
    /**
     * EndDateNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::END_DATE_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::END_DATE_NOT_BE_BLANK_EXCEPTION);
    }
}