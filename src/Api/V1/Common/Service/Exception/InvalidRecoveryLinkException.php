<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidRecoveryLinkException extends \RuntimeException
{
    /**
     * InvalidRecoveryLinkException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RECOVERY_LINK_INVALID);
    }
}
