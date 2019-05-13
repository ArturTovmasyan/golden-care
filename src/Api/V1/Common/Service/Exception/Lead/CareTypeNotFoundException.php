<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class CareTypeNotFoundException extends \RuntimeException
{
    /**
     * CareTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::LEAD_CARE_TYPE_NOT_FOUND_EXCEPTION);
    }
}