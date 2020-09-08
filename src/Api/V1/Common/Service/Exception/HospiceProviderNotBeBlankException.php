<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class HospiceProviderNotBeBlankException extends ApiException
{
    /**
     * HospiceProviderNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::HOSPICE_PROVIDER_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::HOSPICE_PROVIDER_NOT_BE_BLANK_EXCEPTION);
    }
}