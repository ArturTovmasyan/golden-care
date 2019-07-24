<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ReportMisconfigurationException extends ApiException
{
    /**
     * ReportMisconfigurationException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::REPORT_MISCONFIGURATION_EXCEPTION]['message'], ResponseCode::REPORT_MISCONFIGURATION_EXCEPTION);
    }
}
