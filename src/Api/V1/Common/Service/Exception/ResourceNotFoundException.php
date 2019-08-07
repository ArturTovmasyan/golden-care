<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResourceNotFoundException extends ApiException
{
    /**
     * ResourceNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESOURCE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESOURCE_NOT_FOUND_EXCEPTION);
    }
}