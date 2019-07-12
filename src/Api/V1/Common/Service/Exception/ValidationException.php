<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class ValidationException extends \RuntimeException
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * ValidationException constructor.
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct(ResponseCode::$titles[ResponseCode::VALIDATION_ERROR_EXCEPTION]['message'], ResponseCode::VALIDATION_ERROR_EXCEPTION);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}