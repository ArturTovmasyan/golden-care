<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentAdmitOnlyOneTimeException extends ApiException
{
    /**
     * ResidentAdmitOnlyOneTimeException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_ADMIT_ONLY_ONE_TIME_EXCEPTION]['message'], ResponseCode::RESIDENT_ADMIT_ONLY_ONE_TIME_EXCEPTION);
    }
}