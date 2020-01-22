<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FacilityRoomTypeNotFoundException extends ApiException
{
    /**
     * FacilityRoomTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FACILITY_ROOM_TYPE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::FACILITY_ROOM_TYPE_NOT_FOUND_EXCEPTION);
    }
}