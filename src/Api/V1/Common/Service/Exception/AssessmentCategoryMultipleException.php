<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentCategoryMultipleException extends \RuntimeException
{
    /**
     * AssessmentCategoryMultipleException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::ASSESSMENT_CATEGORY_MULTIPLE_EXCEPTION);
    }
}