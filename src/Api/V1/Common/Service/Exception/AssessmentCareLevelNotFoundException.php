<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentCareLevelNotFoundException extends ApiException
{
    /**
     * AssessmentCareLevelNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ASSESSMENT_CARE_LEVEL_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ASSESSMENT_CARE_LEVEL_NOT_FOUND_EXCEPTION);
    }
}