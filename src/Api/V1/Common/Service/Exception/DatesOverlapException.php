<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DatesOverlapException extends ApiException
{
    /**
     * DatesOverlapException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DATES_OVERLAP_EXCEPTION]['message'], ResponseCode::DATES_OVERLAP_EXCEPTION);
    }
}