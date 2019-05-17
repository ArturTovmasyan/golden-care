<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class OrganizationNotFoundException extends \RuntimeException
{
    /**
     * OrganizationNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::LEAD_ORGANIZATION_NOT_FOUND_EXCEPTION);
    }
}