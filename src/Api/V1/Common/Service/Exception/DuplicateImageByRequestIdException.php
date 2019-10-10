<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DuplicateImageByRequestIdException extends ApiException
{
    /**
     * DuplicateImageByRequestIdException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DUPLICATE_IMAGE_BY_REQUEST_ID_EXCEPTION]['message'], ResponseCode::DUPLICATE_IMAGE_BY_REQUEST_ID_EXCEPTION);
    }
}