<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class LeadNotFoundException extends \RuntimeException
{
    /**
     * LeadNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::LEAD_LEAD_NOT_FOUND_EXCEPTION);
    }
}