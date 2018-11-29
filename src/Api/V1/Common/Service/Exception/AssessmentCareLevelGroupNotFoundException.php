<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentCareLevelGroupNotFoundException extends \RuntimeException
{
    /**
     * AssessmentCareLevelGroupNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::ASSESSMENT_CARE_LEVEL_GROUP_NOT_FOUND_EXCEPTION);
    }
}