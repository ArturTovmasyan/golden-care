<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class RoomTypeFacilityChangedException extends ApiException
{
    /**
     * RoomTypeFacilityChangedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FACILITY_ROOM_TYPE_FACILITY_CHANGED_EXCEPTION]['message'], ResponseCode::FACILITY_ROOM_TYPE_FACILITY_CHANGED_EXCEPTION);
    }
}