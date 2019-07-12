<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class GridOptionsNotFoundException extends \RuntimeException
{
    /**
     * GridOptionsNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::GRID_OPTIONS_NOT_FOUND_EXCEPTION]['message'], ResponseCode::GRID_OPTIONS_NOT_FOUND_EXCEPTION);
    }
}
