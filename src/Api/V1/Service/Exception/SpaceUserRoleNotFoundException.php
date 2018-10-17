<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SpaceUserRoleNotFoundException extends \RuntimeException
{
    /**
     * SpaceUserRoleNotFoundException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Invalid user access for space',
        int $code = Response::HTTP_BAD_REQUEST,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}