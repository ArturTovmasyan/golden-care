<?php

namespace  App\Model;

class ResidentRentType
{
    /**
     * Types
     */
    const MONTHLY = 1;
    const WEEKLY = 2;
    const DAILY = 3;
    const HOURLY = 4;

    /**
     * @var array
     */
    private static $typeNames = [
        self::MONTHLY => 'Monthly',
        self::WEEKLY => 'Weekly',
        self::DAILY => 'Daily',
        self::HOURLY => 'Hourly',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Monthly' => self::MONTHLY,
        'Weekly' => self::WEEKLY,
        'Daily' => self::DAILY,
        'Hourly' => self::HOURLY,
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::MONTHLY,
        self::WEEKLY,
        self::DAILY,
        self::HOURLY,
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

