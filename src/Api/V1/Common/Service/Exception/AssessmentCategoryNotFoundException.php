<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentCategoryNotFoundException extends ApiException
{
    /**
     * AssessmentCategoryNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ASSESSMENT_CATEGORY_NOT_FOUND_EXCEPTION]['message'], ResponseCode::ASSESSMENT_CATEGORY_NOT_FOUND_EXCEPTION);
    }
}