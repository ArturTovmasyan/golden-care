<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AssessmentNotFoundException extends \RuntimeException
{
    /**
     * AssessmentNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::ASSESSMENT_NOT_FOUND_EXCEPTION);
    }
}