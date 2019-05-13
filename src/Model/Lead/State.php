<?php

namespace  App\Model\Lead;

class State
{
    const TYPE_OPEN   = 1;
    const TYPE_CLOSED = 2;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_OPEN  => 'Open',
        self::TYPE_CLOSED => 'Closed',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Open' => '1',
        'Closed' => '2',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_OPEN  => 1,
        self::TYPE_CLOSED => 2,
    ];

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
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

