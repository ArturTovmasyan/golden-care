<?php

namespace App\Api\V1\Common\Service\Exception\Lead;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\ApiException;

class LeadFunnelStageNotFoundException extends ApiException
{
    /**
     * LeadFunnelStageNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(ResponseCode::$titles[ResponseCode::LEAD_LEAD_FUNNEL_STAGE_NOT_FOUND_EXCEPTION]['message'], ResponseCode::LEAD_LEAD_FUNNEL_STAGE_NOT_FOUND_EXCEPTION);
    }
}