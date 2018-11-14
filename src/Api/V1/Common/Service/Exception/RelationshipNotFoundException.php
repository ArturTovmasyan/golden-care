<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class RelationshipNotFoundException extends \RuntimeException
{
    /**
     * RelationshipNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('', ResponseCode::RELATIONSHIP_NOT_FOUND_EXCEPTION);
    }
}