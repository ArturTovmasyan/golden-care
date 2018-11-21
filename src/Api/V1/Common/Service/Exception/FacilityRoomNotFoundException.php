<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FacilityRoomNotFoundException extends \RuntimeException
{
    /**
     * FacilityRoomNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::FACILITY_ROOM_NOT_FOUND_EXCEPTION);
    }
}