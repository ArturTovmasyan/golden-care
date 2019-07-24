<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SalutationNotFoundException extends ApiException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::SALUTATION_NOT_FOUND_EXCEPTION]['message'], ResponseCode::SALUTATION_NOT_FOUND_EXCEPTION);
    }
}