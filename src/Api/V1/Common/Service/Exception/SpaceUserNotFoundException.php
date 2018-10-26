<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SpaceUserNotFoundException extends \RuntimeException
{
    /**
     * SpaceUserNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::INVALID_USER_ACCESS_TO_SPACE);
    }
}