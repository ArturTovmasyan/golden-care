<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class PhoneSinglePrimaryException extends \RuntimeException
{
    /**
     * PhoneSinglePrimaryException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::PHONE_SINGLE_PRIMARY_EXCEPTION]['message'], ResponseCode::PHONE_SINGLE_PRIMARY_EXCEPTION);
    }
}