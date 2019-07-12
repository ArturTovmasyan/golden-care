<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class EventDefinitionNotFoundException extends \RuntimeException
{
    /**
     * EventDefinitionNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::EVENT_DEFINITION_NOT_FOUND_EXCEPTION]['message'], ResponseCode::EVENT_DEFINITION_NOT_FOUND_EXCEPTION);
    }
}