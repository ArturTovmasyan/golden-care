<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ContractAlreadyExistException extends \RuntimeException
{
    /**
     * ContractAlreadyExistException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::CONTRACT_ALREADY_EXIST_EXCEPTION);
    }
}