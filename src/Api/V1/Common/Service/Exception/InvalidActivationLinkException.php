<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidActivationLinkException extends \RuntimeException
{
    /**
     * InvalidActivationLinkException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::ACTIVATION_LINK_INVALID);
    }
}
