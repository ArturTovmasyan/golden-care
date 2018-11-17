<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class RegionNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::REGION_NOT_FOUND_EXCEPTION);
    }
}