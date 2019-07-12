<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FileException extends \RuntimeException
{
    /**
     * FileException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FILE_SYSTEM_EXCEPTION]['message'], ResponseCode::FILE_SYSTEM_EXCEPTION);
    }
}