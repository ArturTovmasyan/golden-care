<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class IncorrectChangeLogException extends \RuntimeException
{
    /**
     * IncorrectChangeLogException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::INCORRECT_CHANGE_LOG_TYPE_EXCEPTION);
    }
}