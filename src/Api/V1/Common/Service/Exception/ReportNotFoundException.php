<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ReportNotFoundException extends \RuntimeException
{
    /**
     * ReportNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::REPORT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::REPORT_NOT_FOUND_EXCEPTION);
    }
}