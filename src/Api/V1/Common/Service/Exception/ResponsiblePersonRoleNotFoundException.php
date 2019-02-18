<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ResponsiblePersonRoleNotFoundException extends \RuntimeException
{
    /**
     * ResponsiblePersonRoleNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RESPONSIBLE_PERSON_ROLE_NOT_FOUND_EXCEPTION);
    }
}