<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PhysicianNotBeBlankException extends \RuntimeException
{
    /**
     * PhysicianNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::PHYSICIAN_NOT_BE_BLANK_EXCEPTION);
    }
}