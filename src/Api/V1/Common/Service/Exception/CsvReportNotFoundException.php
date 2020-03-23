<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class CsvReportNotFoundException extends ApiException
{
    /**
     * CsvReportNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::CSV_REPORT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::CSV_REPORT_NOT_FOUND_EXCEPTION);
    }
}