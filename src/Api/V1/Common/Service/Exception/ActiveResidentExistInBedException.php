<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ActiveResidentExistInBedException extends ApiException
{
    /**
     * ActiveResidentExistInBedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ACTIVE_RESIDENT_EXIST_IN_BED_EXCEPTION]['message'], ResponseCode::ACTIVE_RESIDENT_EXIST_IN_BED_EXCEPTION);
    }
}