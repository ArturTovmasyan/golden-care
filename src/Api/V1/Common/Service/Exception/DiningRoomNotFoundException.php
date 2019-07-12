<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DiningRoomNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DINING_ROOM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::DINING_ROOM_NOT_FOUND_EXCEPTION);
    }
}