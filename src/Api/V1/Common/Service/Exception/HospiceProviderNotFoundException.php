<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class HospiceProviderNotFoundException extends ApiException
{
    /**
     * HospiceProviderNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::HOSPICE_PROVIDER_NOT_FOUND_EXCEPTION]['message'], ResponseCode::HOSPICE_PROVIDER_NOT_FOUND_EXCEPTION);
    }
}