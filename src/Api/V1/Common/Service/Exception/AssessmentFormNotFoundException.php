<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentFormNotFoundException extends ApiException
{
    /**
     * AssessmentFormNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ASSESSMENT_FORM_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ASSESSMENT_FORM_NOT_FOUND_EXCEPTION);
    }
}