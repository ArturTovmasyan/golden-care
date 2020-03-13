<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SubjectNotBeBlankException extends ApiException
{
    /**
     * SubjectNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::SUBJECT_NOT_BE_BLANK_EXCEPTION]['message'], ResponseCode::SUBJECT_NOT_BE_BLANK_EXCEPTION);
    }
}