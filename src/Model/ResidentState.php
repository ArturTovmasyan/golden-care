<?php

namespace App\Model;

class ResidentState
{
    const TYPE_ACTIVE = 'active';
    const TYPE_INACTIVE = 'inactive';
    const TYPE_NO_ADMISSION = 'no-admission';

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_ACTIVE => 'active',
        self::TYPE_INACTIVE => 'inactive',
        self::TYPE_NO_ADMISSION => 'no-admission',
    ];

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }
}

