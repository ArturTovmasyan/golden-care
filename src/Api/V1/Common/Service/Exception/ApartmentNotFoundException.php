<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ApartmentNotFoundException extends ApiException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::APARTMENT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::APARTMENT_NOT_FOUND_EXCEPTION);
    }
}