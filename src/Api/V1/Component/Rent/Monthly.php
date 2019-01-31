<?php

namespace App\Api\V1\Component\Rent;

use App\Model\RentPeriod as Period;
use App\Util\Common\ImtDateTimeInterval;

class Monthly extends RentPeriod
{

    public function __construct()
    {
        $this->period = Period::MONTHLY;
        $this->name = Period::getTypeNames()[Period::MONTHLY];
    }

    public function calculateForInterval(ImtDateTimeInterval $subInterval, $amount)
    {
        $diff = $subInterval->getEnd()->diff($subInterval->getStart());
        $monthsCount = $diff->y * 12 + $diff->m;
        $daysCount = $diff->d;

        // Mick provided formula.
        return $amount * $monthsCount + ($amount * 12 * $daysCount / 365);
    }
}