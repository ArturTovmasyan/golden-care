<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InvalidActivationLinkException extends ApiException
{
    /**
     * InvalidActivationLinkException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::ACTIVATION_LINK_INVALID]['message'], ResponseCode::ACTIVATION_LINK_INVALID);
    }
}
