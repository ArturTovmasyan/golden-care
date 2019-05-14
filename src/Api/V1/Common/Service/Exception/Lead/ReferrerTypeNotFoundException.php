<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class ReferrerTypeNotFoundException extends \RuntimeException
{
    /**
     * ReferrerTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::LEAD_REFERRER_TYPE_NOT_FOUND_EXCEPTION);
    }
}