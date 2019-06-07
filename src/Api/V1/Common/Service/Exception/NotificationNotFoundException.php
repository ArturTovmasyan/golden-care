<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class NotificationNotFoundException extends \RuntimeException
{
    /**
     * NotificationNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::NOTIFICATION_NOT_FOUND_EXCEPTION);
    }
}