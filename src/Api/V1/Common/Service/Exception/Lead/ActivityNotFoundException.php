<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class ActivityNotFoundException extends \RuntimeException
{
    /**
     * ActivityNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_ACTIVITY_NOT_FOUND_EXCEPTION]['message'], ResponseCode::LEAD_ACTIVITY_NOT_FOUND_EXCEPTION);
    }
}