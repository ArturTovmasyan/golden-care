<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class TypeOfCareNotFoundException extends \RuntimeException
{
    /**
     * TypeOfCareNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::TYPE_OF_CARE_NOT_FOUND_EXCEPTION);
    }
}