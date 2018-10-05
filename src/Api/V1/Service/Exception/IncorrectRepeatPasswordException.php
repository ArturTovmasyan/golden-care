<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;

class IncorrectRepeatPasswordException extends \RuntimeException
{
    /**
     * @var string
     */
    public $message = "Password and repeat password do not match";

    /**
     * @var int
     */
    public $code = Response::HTTP_BAD_REQUEST;
}