<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserNotFoundException extends ApiException
{
    /**
     * SpaceNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::USER_NOT_FOUND_EXCEPTION]['message'], ResponseCode::USER_NOT_FOUND_EXCEPTION);
    }
}