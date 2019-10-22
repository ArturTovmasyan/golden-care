<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class ContactOrganizationNotAllowedChangeException extends ApiException
{
    /**
     * ContactOrganizationNotAllowedChangeException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_CONTACT_ORGANIZATION_NOT_ALLOWED_CHANGE_EXCEPTION]['message'], ResponseCode::LEAD_CONTACT_ORGANIZATION_NOT_ALLOWED_CHANGE_EXCEPTION);
    }
}