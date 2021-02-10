<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class NameNotBeBlankException extends ApiException
{
    /**
     * NameNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::NAME_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::NAME_NOT_BE_BLANK_EXCEPTION);
    }
}