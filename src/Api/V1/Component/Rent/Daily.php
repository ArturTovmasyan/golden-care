<?php

namespace App\Api\V1\Component\Rent;

use App\Model\RentPeriod as Period;
use App\Util\Common\ImtDateTimeInterval;

class Daily extends RentPeriod
{

    public function __construct()
    {
        $this->period = Period::DAILY;
        $this->name = Period::getTypeNames()[Period::DAILY];
    }

    public function calculateForInterval(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $daysCount = $diff->days;
        $hoursCount = $diff->h;

        return $amount * $daysCount + ($amount * $hoursCount / 24);
    }

    public function calculateForRoomRentInterval(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $daysCount = $diff->days;
        $hoursCount = $diff->h + 1;

        return $amount * $daysCount + ($amount * $hoursCount / 24);
    }

    public function calculateForFacilityDashboard(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $daysCount = $diff->days;
        $hoursCount = $diff->h + 1;

        return $amount * $daysCount + ($amount * $hoursCount / 24);
    }
}