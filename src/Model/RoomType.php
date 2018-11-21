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
     * @return array
     */
    public static function getTypeNames()
    {
        return self::$typeNames;
    }
}

