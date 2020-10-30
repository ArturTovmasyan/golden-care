<?php

namespace App\Model;

class SourcePeriod
{
    /**
     * Types
     */
    const DAILY = 1;
//    const WEEKLY = 2;
    const MONTHLY = 3;

    /**
     * @var array
     */
    private static $typeNames = [
        self::DAILY => 'Daily',
//        self::WEEKLY => 'Weekly',
        self::MONTHLY => 'Monthly',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Daily' => '1',
//        'Weekly' => '2',
        'Monthly' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::DAILY,
//        self::WEEKLY,
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

