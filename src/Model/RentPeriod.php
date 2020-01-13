<?php

namespace App\Model;

class RentPeriod
{
    /**
     * Types
     */
    const HOURLY = 1;
    const DAILY = 2;
    const WEEKLY = 3;
    const MONTHLY = 4;

    /**
     * @var array
     */
    private static $typeNames = [
        self::HOURLY => 'Hourly',
        self::DAILY => 'Daily',
        self::WEEKLY => 'Weekly',
        self::MONTHLY => 'Monthly',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Hourly' => '1',
        'Daily' => '2',
        'Weekly' => '3',
        'Monthly' => '4',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::HOURLY,
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
    ];

    /**
     * @return array
     */
    public static function getTypeNames()
    {
        return self::$typeNames;
    }

    /**
     * @return array
     */
    public static function getTypeDefaultNames()
    {
        return self::$typeDefaultNames;
    }

    /**
     * @return array
     */
    public static function getTypeValues()
    {
        return self::$typeValues;
    }
}

