<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ActiveResidentExistInRoomException extends ApiException
{
    /**
     * ActiveResidentExistInRoomException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ACTIVE_RESIDENT_EXIST_IN_ROOM_EXCEPTION]['message'], ResponseCode::ACTIVE_RESIDENT_EXIST_IN_ROOM_EXCEPTION);
    }
}