<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class IncorrectOwnerTypeException extends ApiException
{
    /**
     * IncorrectOwnerTypeException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INCORRECT_LEAD_OWNER_TYPE_EXCEPTION]['message'], ResponseCode::INCORRECT_LEAD_OWNER_TYPE_EXCEPTION);
    }
}