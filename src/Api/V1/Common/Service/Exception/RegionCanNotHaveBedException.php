<?php

namespace App\Api\V1\Common\Service\Exception;

use App\Api\V1\Common\Model\ResponseCode;

class RegionCanNotHaveBedException extends ApiException
{
    /**
     * RegionCanNotHaveBedException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::REGION_CAN_NOT_HAVE_BED_EXCEPTION]['message'], ResponseCode::REGION_CAN_NOT_HAVE_BED_EXCEPTION);
    }
}