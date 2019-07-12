<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ChangeLogNotFoundException extends \RuntimeException
{
    /**
     * ChangeLogNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CHANGE_LOG_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CHANGE_LOG_NOT_FOUND_EXCEPTION);
    }
}