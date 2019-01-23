<?php

namespace App\Api\V1\Component\Rent;

use App\Model\RentPeriod as Period;
use App\Util\Common\ImtDateTimeInterval;

class Hourly extends RentPeriod
{

    public function __construct()
    {
        $this->period = Period::HOURLY;
        $this->name = Period::getTypeNames()[Period::HOURLY];
    }

    public function calculateForInterval(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $hoursCount = $diff->y * 365 * 24 + $diff->m * 365 / 12 * 24 + $diff->d * 24;
        $minutesCount = $diff->i;

        return $amount * $hoursCount + ($amount * $minutesCount / 60);
    }
}