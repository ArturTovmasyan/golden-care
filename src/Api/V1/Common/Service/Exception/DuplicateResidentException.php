<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DuplicateResidentException extends ApiException
{
    /**
     * DuplicateResidentException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DUPLICATE_RESIDENT_EXCEPTION]['message'], ResponseCode::DUPLICATE_RESIDENT_EXCEPTION);
    }
}