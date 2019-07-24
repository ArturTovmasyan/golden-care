<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentCareLevelGroupNotFoundException extends ApiException
{
    /**
     * AssessmentCareLevelGroupNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ASSESSMENT_CARE_LEVEL_GROUP_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ASSESSMENT_CARE_LEVEL_GROUP_NOT_FOUND_EXCEPTION);
    }
}