<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ApartmentBedNotFoundException extends ApiException
{
    /**
     * ApartmentBedNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::APARTMENT_BED_NOT_FOUND_EXCEPTION]['message'], ResponseCode::APARTMENT_BED_NOT_FOUND_EXCEPTION);
    }
}