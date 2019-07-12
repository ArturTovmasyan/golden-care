<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FolderNotDefinedException extends \RuntimeException
{
    /**
     * FolderNotDefinedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FOLDER_NOT_DEFINED_EXCEPTION]['message'], ResponseCode::FOLDER_NOT_DEFINED_EXCEPTION);
    }
}