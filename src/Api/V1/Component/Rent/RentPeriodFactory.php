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
                new \DateTime($dateTimeStart->format('Y-m-d 00:00:00')),
                new \DateTime($dateTimeEnd->format('Y-m-d 00:00:00'))
            );
            $days = $overlappingInterval->getEnd() !== null ? $overlappingInterval->getEnd()->diff($overlappingInterval->getStart())->days : 0;
            if ($overlappingInterval->getEnd()->format('d') === $overlappingInterval->getEnd()->format('t')) {
                ++$days;
            }
        }

        return array(
            'days' => $days,
        );
    }

    /**
     * @param ImtDateTimeInterval $rentInterval
     * @return array
     */
    public function calculateForMoveReportInterval(ImtDateTimeInterval $rentInterval): array
    {
        $overlappingInterval = ImtDateTimeInterval::getWithDateTimes(
            $rentInterval->getStart(),
            $rentInterval->getEnd()
        );
        $days = $overlappingInterval->getEnd() !== null ? $overlappingInterval->getEnd()->diff($overlappingInterval->getStart())->days : 0;

        return array(
            'days' => $days,
        );
    }

    /**
     * @param ImtDateTimeInterval $rentInterval
     * @param $period
     * @param $amount
     * @return mixed
     */
    public function calculateForFacilityDashboard(ImtDateTimeInterval $rentInterval, $period, $amount)
    {
        $period = $this->getPeriod($period);

        return $period->calculateForFacilityDashboard($rentInterval, $amount);
    }

    /**
     * @param ImtDateTimeInterval $rentInterval
     * @param $period
     * @param $amount
     * @param array|null $awayDaysIntervals
     * @return array
     * @throws \Exception
     */
    public function calculateForRoomRentInterval(ImtDateTimeInterval $rentInterval, $period, $amount, array $awayDaysIntervals = null): array
    {
        $period = $this->getPeriod($period);
        $subIntervalStartFormatted = $this->subInterval->getStart()->format('Y-m-d 00:00:00');
        $subIntervalEndFormatted = $this->subInterval->getEnd()->format('Y-m-d 23:59:59');

        $rentIntervalStartFormatted = $rentInterval->getStart()->format('Y-m-d 00:00:00');
        $rentIntervalEndFormatted = $rentInterval->getEnd()->format('Y-m-d 23:59:59');

        $dateTimeStart = $subIntervalStartFormatted > $rentIntervalStartFormatted ? $subIntervalStartFormatted : $rentIntervalStartFormatted;
        if ($rentInterval->getEnd() === null) {
            $dateTimeEnd = $subIntervalEndFormatted;
        } else {
            $dateTimeEnd = $rentIntervalEndFormatted > $subIntervalEndFormatted ? $subIntervalEndFormatted : $rentIntervalEndFormatted;
        }

        $overlappingInterval = ImtDateTimeInterval::getWithDateTimes(
            new \DateTime($dateTimeStart),
            new \DateTime($dateTimeEnd)
        );
        $days = $overlappingInterval->getEnd()->diff($overlappingInterval->getStart())->days;
        if ($overlappingInterval->getEnd()->format('d') === $overlappingInterval->getEnd()->format('t')) {
            ++$days;
        }

        $absentDaysArray = [];
        $absentDays = 0;
        if (!empty($awayDaysIntervals)) {
            /** @var ImtDateTimeInterval $awayDaysInterval */
            foreach ($awayDaysIntervals as $awayDaysInterval) {
                $absentDaysArray[] = $this->datesOverlap($overlappingInterval->getStart(), $overlappingInterval->getEnd(), $awayDaysInterval->getStart(), $awayDaysInterval->getEnd());
            }

            $absentDays = array_sum($absentDaysArray);
            $days -= $absentDays;

            $overlappingInterval = ImtDateTimeInterval::getWithDateTimes(
                new \DateTime($dateTimeStart),
                (new \DateTime($dateTimeEnd))->modify('-' . $absentDays . 'day')
            );
        }

        return array(
            'amount' => $period->calculateForRoomRentInterval($overlappingInterval, $amount),
            'days' => $days,
            'absentDays' => $absentDays,
        );
    }

    public function datesOverlap($startOne, $endOne, $startTwo, $endTwo) {
        if($startOne <= $endTwo && $endOne >= $startTwo) { //If the dates overlap
            return min($endOne,$endTwo)->diff(max($startTwo,$startOne))->days + 1; //return how many days overlap
        }

        return 0; //Return 0 if there is no overlap
    }

    /**
     * @param ImtDateTimeInterval $subInterval
     * @param ImtDateTimeInterval $rentInterval
     * @param $period
     * @param $amount
     * @return array
     */
    public function calculateForRoomRentByYearInterval(ImtDateTimeInterval $subInterval, ImtDateTimeInterval $rentInterval, $period, $amount): array
    {
        $period = $this->getPeriod($period);
        $subIntervalStartFormatted = $subInterval->getStart()->format('Y-m-d 00:00:00');
        $subIntervalEndFormatted = $subInterval->getEnd()->format('Y-m-d 23:59:59');

        $rentIntervalStartFormatted = $rentInterval->getStart()->format('Y-m-d 00:00:00');
        $rentIntervalEndFormatted = $rentInterval->getEnd()->format('Y-m-d 23:59:59');


        $dateTimeStart = $subIntervalStartFormatted > $rentIntervalStartFormatted ? $subIntervalStartFormatted : $rentIntervalStartFormatted;
        if ($rentInterval->getEnd() === null) {
            $dateTimeEnd = $subIntervalEndFormatted;
        } else {
            $dateTimeEnd = $rentIntervalEndFormatted > $subIntervalEndFormatted ? $subIntervalEndFormatted : $rentIntervalEndFormatted;
        }

        $overlappingInterval = ImtDateTimeInterval::getWithDateTimes(
            new \DateTime($dateTimeStart),
            new \DateTime($dateTimeEnd)
        );
        $days = $overlappingInterval->getEnd()->diff($overlappingInterval->getStart())->days;
        if ($overlappingInterval->getEnd()->format('d') === $overlappingInterval->getEnd()->format('t')) {
            ++$days;
        }

        return array(
            'amount' => $period->calculateForRoomRentInterval($overlappingInterval, $amount),
            'days' => $days,
        );
    }
}