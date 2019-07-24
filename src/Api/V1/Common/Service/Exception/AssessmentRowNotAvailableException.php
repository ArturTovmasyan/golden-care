<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentRowNotAvailableException extends ApiException
{
    /**
     * AssessmentRowNotAvailableException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ASSESSMENT_ROW_NOT_AVAILABLE_EXCEPTION]['message'], ResponseCode::ASSESSMENT_ROW_NOT_AVAILABLE_EXCEPTION);
    }
}