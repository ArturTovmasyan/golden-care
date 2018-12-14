<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CanNotRemoveBadException extends \RuntimeException
{
    /**
     * CanNotRemoveBadException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::CAN_NOT_REMOVE_BED_EXCEPTION);
    }
}