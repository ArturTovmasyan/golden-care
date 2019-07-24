<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CanNotRemoveBadException extends ApiException
{
    /**
     * CanNotRemoveBadException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CAN_NOT_REMOVE_BED_EXCEPTION]['message'], ResponseCode::CAN_NOT_REMOVE_BED_EXCEPTION);
    }
}