<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class TheNameIsAlreadyInUseInThisSpaceException extends ApiException
{
    /**
     * TheNameIsAlreadyInUseInThisSpaceException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::THE_NAME_IS_ALREADY_IN_USE_IN_THIS_SPACE_EXCEPTION]['message'], ResponseCode::THE_NAME_IS_ALREADY_IN_USE_IN_THIS_SPACE_EXCEPTION);
    }
}