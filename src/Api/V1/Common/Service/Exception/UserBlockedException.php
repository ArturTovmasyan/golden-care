<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class UserBlockedException extends ApiException
{
    /**
     * UserBlockedException constructor.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(
            sprintf(
                ResponseCode::$titles[ResponseCode::INCORRECT_REPORT_PARAMETER]['message'],
                $parameters[0], $parameters[1]
            ),
            ResponseCode::USER_BLOCKED_EXCEPTION);
    }

}