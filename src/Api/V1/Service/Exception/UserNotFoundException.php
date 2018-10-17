<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends \RuntimeException
{
    /**
     * UserNotFoundException constructor.
     * @param string $message
     * @param int $status
     */
    public function __construct(string $message, int $status = Response::HTTP_NOT_FOUND)
    {
        if (empty($message)) {
            $message = 'User not found';
        }

        parent::__construct($message, $status);
    }
}