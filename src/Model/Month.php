<?php

namespace  App\Model;

class Month
{
    const TYPE_JANUARY   = 1;
    const TYPE_FEBRUARY  = 2;
    const TYPE_MARCH     = 3;
    const TYPE_APRIL     = 4;
    const TYPE_MAY       = 5;
    const TYPE_JUNE      = 6;
    const TYPE_JULY      = 7;
    const TYPE_AUGUST    = 8;
    const TYPE_SEPTEMBER = 9;
    const TYPE_OCTOBER   = 10;
    const TYPE_NOVEMBER  = 11;
    const TYPE_DECEMBER  = 12;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_JANUARY   => 'January',
        self::TYPE_FEBRUARY  => 'February',
        self::TYPE_MARCH     => 'March',
        self::TYPE_APRIL     => 'April',
        self::TYPE_MAY       => 'May',
        self::TYPE_JUNE      => 'June',
        self::TYPE_JULY      => 'July',
        self::TYPE_AUGUST    => 'August',
        self::TYPE_SEPTEMBER => 'September',
        self::TYPE_OCTOBER   => 'October',
        self::TYPE_NOVEMBER  => 'November',
        self::TYPE_DECEMBER  => 'December',
    ];

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }
}

