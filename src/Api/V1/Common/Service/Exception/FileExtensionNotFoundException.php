<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FileExtensionNotFoundException extends ApiException
{
    /**
     * FileExtensionNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FILE_EXTENSION_FOUND_EXCEPTION]['message'], ResponseCode::FILE_EXTENSION_FOUND_EXCEPTION);
    }
}