<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ContractNotFoundException extends \RuntimeException
{
    /**
     * ContractNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::CONTRACT_NOT_FOUND_EXCEPTION);
    }
}