<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class IncompleteChunkDataException extends ApiException
{
    /**
     * IncompleteChunkDataException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INCOMPLETE_CHUNK_DATA_EXCEPTION]['message'], ResponseCode::INCOMPLETE_CHUNK_DATA_EXCEPTION);
    }
}