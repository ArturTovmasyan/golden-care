<?php

namespace App\Api\V1\Component\Rent;

use App\Util\Common\ImtDateTimeInterval;

class RentPeriodFactory
{

    private $periods = [];
    private $subInterval;

    private static $factory;

    private function __construct(ImtDateTimeInterval $subInterval)
    {
        $this->subInterval = $subInterval;
    }

    /**
     * @param $subInterval
     * @return RentPeriodFactory
     */
    public static function getFactory($subInterval): RentPeriodFactory
    {
        if (!self::$factory) {
            self::$factory = new RentPeriodFactory($subInterval);
        }
        return self::$factory;
    }

    /**
     * @param ImtDateTimeInterval $rentInterval
     * @param $period
     * @param $amount
     * @return array
     */
    public function calculateForInterval(ImtDateTimeInterval $rentInterval, $period, $amount): array
    {
        $period = $this->getPeriod($period);
        $dateTimeStart = $this->subInterval->getStart() > $rentInterval->getStart() ? $this->subInterval->getStart() : $rentInterval->getStart();
        if ($rentInterval->getEnd() === null) {
            $dateTimeEnd = $this->subInterval->getEnd();
        } else {
            $dateTimeEnd = $rentInterval->getEnd() > $this->subInterval->getEnd() ? $this->subInterval->getEnd() : $rentInterval->getEnd();
        }

        $overlappingInterval = ImtDateTimeInterval::getWithDateTimes(
            $dateTimeStart,
            $dateTimeEnd
        );
        $days = $overlappingInterval->getEnd()->diff($overlappingInterval->getStart())->days;
        return array(
            'amount' => $period->calculateForInterval($overlappingInterval, $amount),
            'days' => $days,
        );
    }

    /**
     * @param ImtDateTimeInterval $contractInterval
     * @return float|int
     */
    public function calculateOccupancyForInterval(ImtDateTimeInterval $contractInterval)
    {
        $dateTimeStart = $this->subInterval->getStart() > $contractInterval->getStart() ? $this->subInterval->getStart() : $contractInterval->getStart();
        if ($contractInterval->getEnd() === null) {
            $dateTimeEnd = $this->subInterval->getEnd();
        } else {
            $dateTimeEnd = $contractInterval->getEnd() > $this->subInterval->getEnd() ? $this->subInterval->getEnd() : $contractInterval->getEnd();
        }

        $overlappingInterval = ImtDateTimeInterval::getWithDateTimes(
            $dateTimeStart,
            $dateTimeEnd
        );

        return ($overlappingInterval->getEnd()->getTimestamp() - $overlappingInterval->getStart()->getTimestamp()) / ($this->subInterval->getEnd()->getTimestamp() - $this->subInterval->getStart()->getTimestamp());
    }

    /**
     * @param $period
     * @return RentPeriod
     */
    public function getPeriod($period): RentPeriod
    {
        if (!isset($this->periods[$period])) {
            $this->periods[$period] = RentPeriod::getPeriodById($period);
        }
        return $this->periods[$period];
    }

    /**
     * @param ImtDateTimeInterval $rentInterval
     * @param ImtDateTimeInterval $subInterval
     * @return array
     */
    public function calculateForReportInterval(ImtDateTimeInterval $rentInterval, ImtDateTimeInterval $subInterval): array
    {
        $dateTimeStart = $subIntervalStart = $subInterval->getStart();
        $dateTimeEnd = $subIntervalEnd = $subInterval->getEnd();
        $rentIntervalStart = $rentInterval->getStart();
        $rentIntervalEnd = $rentInterval->getEnd();

        if ($subIntervalStart < $rentIntervalStart && $subIntervalStart->format('Y') === $rentIntervalStart->format('Y')
            && $subIntervalStart->format('m') === $rentIntervalStart->format('m')) {

            $dateTimeStart = $rentIntervalStart;
        }

        if ($rentIntervalEnd !== null && $subIntervalEnd > $rentIntervalEnd && $subIntervalEnd->format('Y') === $rentIntervalEnd->format('Y')
            && $subIntervalEnd->format('m') === $rentIntervalEnd->format('m')) {

            $dateTimeEnd = $rentIntervalEnd;
        }

        $days = 0;
        if (($dateTimeStart >= $rentIntervalStart && (($rentIntervalEnd !== null && $dateTimeStart <= $rentIntervalEnd) || $rentIntervalEnd === null)) &&
            ($dateTimeEnd >= $rentIntervalStart && (($rentIntervalEnd !== null && $dateTimeEnd <= $rentIntervalEnd) || $rentIntervalEnd === null))) {
            $overlappingInterval = ImtDateTimeInterval::getWithDateTimes(
                $dateTimeStart,
                $dateTimeEnd
            );
            $days = $overlappingInterval->getEnd() !== null ? $overlappingInterval->getEnd()->diff($overlappingInterval->getStart())->days + 1 : 0;
        }

        return array(
            'days' => $days,
        );
    }
}