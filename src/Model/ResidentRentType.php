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
        'Monthly' => '1',
        'Weekly' => '2',
        'Daily' => '3',
        'Hourly' => '4',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::MONTHLY => '1',
        self::WEEKLY => '2',
        self::DAILY => '3',
        self::HOURLY => '4',
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

