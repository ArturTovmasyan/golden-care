<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidSharedRoomException extends ApiException
{
    /**
     * InvalidSharedRoomException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_SHARED_ROOM_EXCEPTION]['message'], ResponseCode::INVALID_SHARED_ROOM_EXCEPTION);
    }
}