<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;

class DuplicateUserException extends \RuntimeException
{
    /**
     * @var string
     */
    public $message = 'User with this email address or username already exist';

    /**
     * @var int
     */
    public $code = Response::HTTP_BAD_REQUEST;
}