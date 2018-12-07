<?php

namespace  App\Model;

class PaymentPeriod
{
    /**
     * Types
     */
    const YEARLY = 1;
    const MONTHLY = 2;
    const WEEKLY = 3;
    const DAILY = 4;
    const HOURLY = 5;

    /**
     * @var array
     */
    private static $typeNames = [
        self::YEARLY => 'Yearly',
        self::MONTHLY => 'Monthly',
        self::WEEKLY => 'Weekly',
        self::DAILY => 'Daily',
        self::HOURLY => 'Hourly',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Yearly' => '1',
        'Monthly' => '2',
        'Weekly' => '3',
        'Daily' => '4',
        'Hourly' => '5',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::YEARLY,
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

