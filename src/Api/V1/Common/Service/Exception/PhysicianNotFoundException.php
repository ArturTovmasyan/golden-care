<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PhysicianNotFoundException extends \RuntimeException
{
    /**
     * PhysicianNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::PHYSICIAN_NOT_FOUND_EXCEPTION);
    }
}