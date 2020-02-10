<?php

namespace App\Api\V1\Component\Rent;

use App\Model\RentPeriod as Period;
use App\Util\Common\ImtDateTimeInterval;

class Weekly extends RentPeriod
{

    public function __construct()
    {
        $this->period = Period::WEEKLY;
        $this->name = Period::getTypeNames()[Period::WEEKLY];
    }

    public function calculateForInterval(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $weeksCount = $diff->y * 365 / 7 + $diff->m * 365 / 12 / 7;
        $daysCount = $diff->d;

        return $amount * $weeksCount + ($amount * $daysCount / 7);
    }

    public function calculateForRoomRentInterval(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $weeksCount = $diff->y * 365 / 7 + $diff->m * 365 / 12 / 7;
        $daysCount = $diff->d + 1;

        return $amount * $weeksCount + ($amount * $daysCount / 7);
    }

    public function calculateForFacilityDashboard(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $weeksCount = $diff->y * 365 / 7 + $diff->m * 365 / 12 / 7;
        $daysCount = $diff->d + 1;

        return $amount * $weeksCount + ($amount * $daysCount / 7);
    }
}