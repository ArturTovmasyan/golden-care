<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class EmailReviewTypeNotFoundException extends ApiException
{
    /**
     * EmailReviewTypeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_EMAIL_REVIEW_TYPE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::LEAD_EMAIL_REVIEW_TYPE_NOT_FOUND_EXCEPTION);
    }
}