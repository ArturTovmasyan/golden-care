<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class AdditionalDateNotBeBlankException extends \RuntimeException
{
    /**
     * AdditionalDateNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::ADDITIONAL_DATE_NOT_BE_BLANK_EXCEPTION);
    }
}