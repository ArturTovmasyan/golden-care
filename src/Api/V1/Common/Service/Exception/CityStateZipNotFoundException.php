<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CityStateZipNotFoundException extends ApiException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CITY_STATE_ZIP_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CITY_STATE_ZIP_NOT_FOUND_EXCEPTION);
    }
}