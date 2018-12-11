<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ApartmentBedNotFoundException extends \RuntimeException
{
    /**
     * ApartmentBedNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::APARTMENT_BED_NOT_FOUND_EXCEPTION);
    }
}