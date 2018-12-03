<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResponsiblePersonNotBeBlankException extends \RuntimeException
{
    /**
     * ResponsiblePersonNotBeBlankException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESPONSIBLE_PERSON_NOT_BE_BLANK_EXCEPTION);
    }
}