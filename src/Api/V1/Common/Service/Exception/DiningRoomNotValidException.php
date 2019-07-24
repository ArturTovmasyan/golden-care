<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DiningRoomNotValidException extends ApiException
{
    /**
     * DiningRoomNotValidException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DINING_ROOM_NOT_VALID_EXCEPTION]['message'], ResponseCode::DINING_ROOM_NOT_VALID_EXCEPTION);
    }
}