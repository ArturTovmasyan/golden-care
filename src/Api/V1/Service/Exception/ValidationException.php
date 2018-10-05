<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;

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
        $message      = 'Validation error';
        $this->errors = $errors;

        parent::__construct($message, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}