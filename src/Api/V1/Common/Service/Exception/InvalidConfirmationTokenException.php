<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidConfirmationTokenException extends \RuntimeException
{
    /**
     * InvalidConfirmationTokenException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INVALID_CONFIRMATION_TOKEN_EXCEPTION]['message'], ResponseCode::INVALID_CONFIRMATION_TOKEN_EXCEPTION);
    }
}