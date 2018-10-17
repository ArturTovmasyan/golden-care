<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UserAlreadyJoinedException extends \RuntimeException
{
    /**
     * UserAlreadyJoinedException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "User already joined to team", int $code = Response::HTTP_BAD_REQUEST, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}