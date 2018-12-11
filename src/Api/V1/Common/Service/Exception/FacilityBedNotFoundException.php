<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FacilityBedNotFoundException extends \RuntimeException
{
    /**
     * FacilityBedNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::FACILITY_BED_NOT_FOUND_EXCEPTION);
    }
}