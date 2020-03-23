<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CsvReportHashHasExpiredException extends ApiException
{
    /**
     * CsvReportHashHasExpiredException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CSV_REPORT_HASH_HAS_EXPIRED]['message'], ResponseCode::CSV_REPORT_HASH_HAS_EXPIRED);
    }
}