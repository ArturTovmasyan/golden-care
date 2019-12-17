<?php

namespace  App\Model;

class RepeatType
{
    /**
     * Types
     */
    const EVERY_DAY = 1;
    const EVERY_WEEK = 2;
    const EVERY_MONTH = 3;

    /**
     * @var array
     */
    private static $typeNames = [
        self::EVERY_DAY => 'Every Day',
        self::EVERY_WEEK => 'Every Week',
        self::EVERY_MONTH => 'Every Month',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Every Day' => '1',
        'Every Week' => '2',
        'Every Month' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::EVERY_DAY,
        self::EVERY_WEEK,
        self::EVERY_MONTH,
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

