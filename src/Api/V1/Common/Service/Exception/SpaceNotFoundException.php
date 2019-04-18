<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SpaceNotFoundException extends \RuntimeException
{
    /**
     * SpaceNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::SPACE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::SPACE_NOT_FOUND_EXCEPTION);
    }
}