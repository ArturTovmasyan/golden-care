<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class DietNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::DIET_NOT_FOUND_EXCEPTION]['message'], ResponseCode::DIET_NOT_FOUND_EXCEPTION);
    }
}