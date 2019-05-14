<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class ActivityTypeNotFoundException extends \RuntimeException
{
    /**
     * ActivityTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::LEAD_ACTIVITY_TYPE_NOT_FOUND_EXCEPTION);
    }
}