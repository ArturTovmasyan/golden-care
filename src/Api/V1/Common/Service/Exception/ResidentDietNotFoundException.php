<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentDietNotFoundException extends \RuntimeException
{
    /**
     * ResidentDietNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESIDENT_DIET_NOT_FOUND_EXCEPTION);
    }
}