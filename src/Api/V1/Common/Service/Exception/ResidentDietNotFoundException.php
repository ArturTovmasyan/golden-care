<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentDietNotFoundException extends ApiException
{
    /**
     * ResidentDietNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_DIET_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_DIET_NOT_FOUND_EXCEPTION);
    }
}