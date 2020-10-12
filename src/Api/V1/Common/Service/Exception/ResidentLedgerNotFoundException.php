<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResidentLedgerNotFoundException extends ApiException
{
    /**
     * ResidentLedgerNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::RESIDENT_LEDGER_NOT_FOUND_EXCEPTION]['message'], ResponseCode::RESIDENT_LEDGER_NOT_FOUND_EXCEPTION);
    }
}