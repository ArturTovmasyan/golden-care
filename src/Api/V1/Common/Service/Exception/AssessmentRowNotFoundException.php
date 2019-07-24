<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentRowNotFoundException extends ApiException
{
    /**
     * AssessmentRowNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ASSESSMENT_ROW_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ASSESSMENT_ROW_NOT_FOUND_EXCEPTION);
    }
}