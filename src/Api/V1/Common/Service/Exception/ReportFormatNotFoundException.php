<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ReportFormatNotFoundException extends \RuntimeException
{
    /**
     * ReportFormatNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::REPORT_FORMAT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::REPORT_FORMAT_NOT_FOUND_EXCEPTION);
    }
}