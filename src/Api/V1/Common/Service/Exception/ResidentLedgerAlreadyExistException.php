<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentLedgerAlreadyExistException extends ApiException
{
    /**
     * ResidentLedgerAlreadyExistException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_LEDGER_ALREADY_EXIST_EXCEPTION]['message'], ResponseCode::RESIDENT_LEDGER_ALREADY_EXIST_EXCEPTION);
    }
}