<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FacilityNotFoundException extends \RuntimeException
{
    /**
     * FacilityNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::FACILITY_NOT_FOUND_EXCEPTION);
    }
}