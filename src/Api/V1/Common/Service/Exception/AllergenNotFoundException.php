<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AllergenNotFoundException extends \RuntimeException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ALLERGEN_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ALLERGEN_NOT_FOUND_EXCEPTION);
    }
}