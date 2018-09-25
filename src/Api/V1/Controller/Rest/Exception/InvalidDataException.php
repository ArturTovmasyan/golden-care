<?php

namespace App\Api\V1\Controller\Rest\Exception;

use Throwable;

class InvalidDataException extends \RuntimeException
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * IncorrectPasswordException constructor.
     * @param string $message
     * @param int $code
     * @param array $errors
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, $errors = [], Throwable $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}