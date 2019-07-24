<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FacilityRoomNotFoundException extends ApiException
{
    /**
     * FacilityRoomNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FACILITY_ROOM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::FACILITY_ROOM_NOT_FOUND_EXCEPTION);
    }
}