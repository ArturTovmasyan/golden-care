<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InsuranceCompanyNotFoundException extends ApiException
{
    /**
     * InsuranceCompanyNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::INSURANCE_COMPANY_NOT_FOUND_EXCEPTION]['message'], ResponseCode::INSURANCE_COMPANY_NOT_FOUND_EXCEPTION);
    }
}