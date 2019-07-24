<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ApartmentRoomNotFoundException extends ApiException
{
    /**
     * ApartmentRoomNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::APARTMENT_ROOM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::APARTMENT_ROOM_NOT_FOUND_EXCEPTION);
    }
}