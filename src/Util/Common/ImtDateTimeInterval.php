<?php

namespace App\Util\Common;

/**
 * Class ImtDateTimeInterval.
 */
class ImtDateTimeInterval
{
    /**
     * @var \DateTime
     */
    private $start;
    /**
     * @var \DateTime
     */
    private $end;

    /**
     * ImtDateTimeInterval constructor.
     * @param \DateTime $start
     * @param \DateTime|null $end
     */
    private function __construct(\DateTime $start, \DateTime $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @param $dayStart
     * @param $dayEnd
     * @return ImtDateTimeInterval
     */
    public static function getWithDays($dayStart, $dayEnd): ImtDateTimeInterval
    {
        $start = new \DateTime($dayStart);
        $start->setTime(0, 0, 1);
        $end = new \DateTime($dayEnd);
        $end->setTime(23, 59, 59);

        return new self($start, $end);
    }

    /**
     * @param \DateTime $dateTimeStart
     * @param \DateTime|null $dateTimeEnd
     * @return ImtDateTimeInterval
     */
    public static function getWithDateTimes(\DateTime $dateTimeStart, \DateTime $dateTimeEnd = null): ImtDateTimeInterval
    {
        return new self($dateTimeStart, $dateTimeEnd);
    }

    /**
     * @param $year
     * @param $month
     * @return ImtDateTimeInterval
     */
    public static function getWithMonthAndYear($year, $month): ImtDateTimeInterval
    {
        $intervalStart = new \DateTime($year . '-' . $month . '-' . '01 00:00:00');
        $intervalEnd = clone $intervalStart;
        $intervalEnd->add(new \DateInterval('P1M'));

        return new self($intervalStart, $intervalEnd);
    }

    /**
     * @param $year
     * @param $month
     * @return ImtDateTimeInterval
     */
    public static function getDateDiffForMonthAndYear($year, $month): ImtDateTimeInterval
    {
        $date = new \DateTime($year . '-' . $month . '-' . '15 00:00:00');
        $date = $date->format('Y-m-d');
        $intervalStart = new \DateTime(date('Y-m-d H:i:s', strtotime($date . ' -1 month')));
        $intervalEnd = new \DateTime($year . '-' . $month . '-' . '15 00:00:00');

        return new self($intervalStart, $intervalEnd);
    }

    /**
     * @return ImtDateTimeInterval
     */
    public static function getYearDiffForDatePicker(): ImtDateTimeInterval
    {
        $yearEnd = new \DateTime('now');
        $year = $yearEnd->format('Y');
        $yearStart = new \DateTime(date('Y-m-d H:i:s', strtotime($year . ' -100 year')));

        return new self($yearStart, $yearEnd);
    }

    /**
     * @param \DateTime $date
     * @return ImtDateTimeInterval
     */
    public static function getWithWeek(\DateTime $date): ImtDateTimeInterval
    {
        $ts = strtotime($date->format('Y-m-d h:i:s'));
        $start = (date('w', $ts) === 0) ? $ts : strtotime('last sunday', $ts);

        return new self(new \DateTime(date('Y-m-d', $start)), new \DateTime(date('Y-m-d', strtotime('next saturday', $start))));
    }

    /**
     * @return int
     */
    public function getDurationInSecs(): int
    {
        $now = new \DateTime('now');

        return $this->end->getTimestamp() - ($this->start ? $this->start->getTimestamp() : $now->getTimestamp());
    }

    /**
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param null $format
     * @return string
     */
    public function getStartFormatted($format = null): string
    {
        if ($format) {
            return $this->start->format($format);
        }

        return $this->start->format('Y-m-d h:i:s');
    }

    /**
     * @param null $format
     * @return string
     */
    public function getEndFormatted($format = null): string
    {
        if ($format) {
            return $this->end->format($format);
        }

        return $this->end->format('Y-m-d h:i:s');
    }
}
