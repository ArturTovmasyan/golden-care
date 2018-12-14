<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ContractActionNotFoundException extends \RuntimeException
{
    /**
     * ContractActionNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::CONTRACT_ACTION_NOT_FOUND_EXCEPTION);
    }
}