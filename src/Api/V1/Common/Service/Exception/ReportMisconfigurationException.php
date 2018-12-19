<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ReportMisconfigurationException extends \RuntimeException
{
    /**
     * ReportMisconfigurationException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::REPORT_MISCONFIGURATION_EXCEPTION);
    }
}
