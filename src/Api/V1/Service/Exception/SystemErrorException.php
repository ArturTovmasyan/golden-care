<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;

class SystemErrorException extends \RuntimeException
{
    /**
     * @var string
     */
    public $message = "System Error";

    /**
     * @var int
     */
    public $code = Response::HTTP_INTERNAL_SERVER_ERROR;
}