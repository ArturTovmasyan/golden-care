<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class SpaceHaventAccessToRoleException extends \RuntimeException
{
    /**
     * SpaceHaventAccessToRoleException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::SPACE_HAVE_NOT_ACCESS_TO_ROLE_EXCEPTION]['message'], ResponseCode::SPACE_HAVE_NOT_ACCESS_TO_ROLE_EXCEPTION);
    }
}