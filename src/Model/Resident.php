<?php

namespace  App\Model;

class Resident
{
    /**
     * States
     */
    const ACTIVE   = 1;
    const INACTIVE = 2;

    /**
     * @var array
     */
    private static $stateNames = [
        self::ACTIVE   => 'Active',
        self::INACTIVE => 'Inactive',
    ];

    /**
     * @return array
     */
    public static function getStateNames()
    {
        return self::$stateNames;
    }
}

