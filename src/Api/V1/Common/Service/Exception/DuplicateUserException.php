<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DuplicateUserException extends \RuntimeException
{
    /**
     * DuplicateUserException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DUPLICATE_USER_EXCEPTION]['message'], ResponseCode::DUPLICATE_USER_EXCEPTION);
    }
}