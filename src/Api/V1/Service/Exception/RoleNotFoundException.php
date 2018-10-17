<?php

namespace App\Api\V1\Service\Exception;

use Symfony\Component\HttpFoundation\Response;

class RoleNotFoundException extends \RuntimeException
{
    /**
     * UserNotFoundException constructor.
     * @param int $status
     */
    public function __construct(int $status = Response::HTTP_NOT_FOUND)
    {
        parent::__construct('Role not found', $status);
    }
}