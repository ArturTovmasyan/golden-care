<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class NotificationTypeNotFoundException extends ApiException
{
    /**
     * NotificationTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::NOTIFICATION_TYPE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::NOTIFICATION_TYPE_NOT_FOUND_EXCEPTION);
    }
}