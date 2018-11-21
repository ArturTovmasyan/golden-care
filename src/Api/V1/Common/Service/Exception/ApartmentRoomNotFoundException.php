<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ApartmentRoomNotFoundException extends \RuntimeException
{
    /**
     * ApartmentRoomNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::APARTMENT_ROOM_NOT_FOUND_EXCEPTION);
    }
}