<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class InsuranceCompanyNotFoundException extends \RuntimeException
{
    /**
     * InsuranceCompanyNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::INSURANCE_COMPANY_NOT_FOUND_EXCEPTION);
    }
}