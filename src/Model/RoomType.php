<?php

namespace  App\Model;

class RoomType
{
    /**
     * Types
     */
    const PRIVATE = 1;
    const SEMI_PRIVATE = 2;

    /**
     * @var array
     */
    private static $typeNames = [
        self::PRIVATE => 'Private',
        self::SEMI_PRIVATE => 'Semi-Private',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Private' => '1',
        'Semi-Private' => '2',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::PRIVATE => '1',
        self::SEMI_PRIVATE => '2',
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

