<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;

class IncorrectOwnerTypeException extends \RuntimeException
{
    /**
     * IncorrectOwnerTypeException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::INCORRECT_LEAD_OWNER_TYPE_EXCEPTION);
    }
}