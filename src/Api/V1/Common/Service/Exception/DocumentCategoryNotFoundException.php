<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DocumentCategoryNotFoundException extends ApiException
{
    /**
     * DocumentCategoryNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DOCUMENT_CATEGORY_NOT_FOUND_EXCEPTION]['message'], ResponseCode::DOCUMENT_CATEGORY_NOT_FOUND_EXCEPTION);
    }
}