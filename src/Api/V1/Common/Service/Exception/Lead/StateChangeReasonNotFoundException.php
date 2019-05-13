<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class StateChangeReasonNotFoundException extends \RuntimeException
{
    /**
     * StateChangeReasonNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::LEAD_STATE_CHANGE_REASON_NOT_FOUND_EXCEPTION);
    }
}