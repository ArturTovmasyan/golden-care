<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class FeedbackUnknownException extends ApiException
{
    /**
     * RoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::FEEDBACK_EXCEPTION]['message'], ResponseCode::FEEDBACK_EXCEPTION);
    }
}