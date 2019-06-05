<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UnhandledImageOwnerException extends \RuntimeException
{
    /**
     * UnhandledImageOwnerException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::UNHANDLED_IMAGE_OWNER_EXCEPTION);
    }
}