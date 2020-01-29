<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidPrivateRoomException extends ApiException
{
    /**
     * InvalidPrivateRoomException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_PRIVATE_ROOM_EXCEPTION]['message'], ResponseCode::INVALID_PRIVATE_ROOM_EXCEPTION);
    }
}