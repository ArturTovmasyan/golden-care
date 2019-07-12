<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FileExtensionException extends \RuntimeException
{
    /**
     * FileException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FILE_EXTENSION_NOT_SUPPORTED]['message'], ResponseCode::FILE_EXTENSION_NOT_SUPPORTED);
    }
}