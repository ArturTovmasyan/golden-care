<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResponsiblePersonNotFoundException extends ApiException
{
    /**
     * ResponsiblePersonNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESPONSIBLE_PERSON_NOT_FOUND_EXCEPTION);
    }
}