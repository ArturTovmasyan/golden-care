<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class ContactNotFoundException extends ApiException
{
    /**
     * ContactNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_CONTACT_NOT_FOUND_EXCEPTION]['message'], ResponseCode::LEAD_CONTACT_NOT_FOUND_EXCEPTION);
    }
}