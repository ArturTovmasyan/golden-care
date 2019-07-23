<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DocumentNotFoundException extends \RuntimeException
{
    /**
     * DocumentNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DOCUMENT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::DOCUMENT_NOT_FOUND_EXCEPTION);
    }
}