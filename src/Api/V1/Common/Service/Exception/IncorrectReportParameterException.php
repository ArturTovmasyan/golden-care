<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class IncorrectReportParameterException extends \RuntimeException
{
    /**
     * IncorrectReportParameterException constructor.
     */
    public function __construct(array $parameters)
    {
        parent::__construct(
            sprintf(
                ResponseCode::$titles[ResponseCode::INCORRECT_REPORT_PARAMETER]['message'],
                implode(',', $parameters)
            ),
            ResponseCode::INCORRECT_REPORT_PARAMETER);
    }
}
