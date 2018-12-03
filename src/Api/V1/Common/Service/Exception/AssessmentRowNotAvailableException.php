<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentRowNotAvailableException extends \RuntimeException
{
    /**
     * AssessmentRowNotAvailableException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::ASSESSMENT_ROW_NOT_AVAILABLE_EXCEPTION);
    }
}