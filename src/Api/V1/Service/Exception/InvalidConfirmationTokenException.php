<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;

class InvalidConfirmationTokenException extends \RuntimeException
{
    /**
     * @var string
     */
    public $message = "Invalid confirmation token";

    /**
     * @var int
     */
    public $code = Response::HTTP_BAD_REQUEST;
}