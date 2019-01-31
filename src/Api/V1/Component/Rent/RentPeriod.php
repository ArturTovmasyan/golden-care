<?php

namespace App\Api\V1\Component\Rent;

use App\Api\V1\Common\Service\Exception\UnhandledRentPeriodException;
use App\Util\Common\ImtDateTimeInterval;
use App\Model\RentPeriod as Period;

abstract class RentPeriod
{
    // Period identifier.
    /** @var  int */
    protected $period;

    // Period name.
    /** @var  string */
    protected $name;

    /**
     * Init period, name.
     */
    abstract public function __construct();

    /**
     * @param ImtDateTimeInterval $subInterval
     * @param $amount
     * @return mixed
     */
    abstract public function calculateForInterval(ImtDateTimeInterval $subInterval, $amount);

    /**
     * @return int
     */
    public function getPeriod(): ?int
    {
        return $this->period;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public static function getPeriodById($idPeriod)
    {
        switch ($idPeriod) {
            case Period::HOURLY:
                return new Hourly();
            case Period::DAILY:
                return new Daily();
            case Period::WEEKLY:
                return new Weekly();
            case Period::MONTHLY:
                return new Monthly();
            default:
                throw new UnhandledRentPeriodException();
        }
    }

    public static function getPeriods(): void
    {
        Period::getTypeNames();
    }
}