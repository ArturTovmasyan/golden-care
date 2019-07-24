<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentDocumentNotFoundException extends ApiException
{
    /**
     * ResidentDocumentNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_DOCUMENT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_DOCUMENT_NOT_FOUND_EXCEPTION);
    }
}