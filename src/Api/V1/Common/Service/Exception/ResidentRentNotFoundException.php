<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentRentNotFoundException extends \RuntimeException
{
    /**
     * ResidentRentNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_RENT_NOT_FOUND_EXCEPTION);
    }
}