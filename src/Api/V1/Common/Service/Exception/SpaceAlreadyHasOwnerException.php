<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SpaceAlreadyHasOwnerException extends ApiException
{
    /**
     * SpaceAlreadyHasOwnerException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::SPACE_ALREADY_HAS_OWNER_EXCEPTION]['message'], ResponseCode::SPACE_ALREADY_HAS_OWNER_EXCEPTION);
    }
}
