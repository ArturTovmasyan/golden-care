<?php

namespace  App\Model;

class GroupType
{
    const TYPE_FACILITY  = 1;
    const TYPE_APARTMENT = 2;
    const TYPE_REGION    = 3;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_FACILITY  => 'Facility',
        self::TYPE_APARTMENT => 'Apartment',
        self::TYPE_REGION    => 'Region',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Facility' => '1',
        'Apartment' => '2',
        'Region' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_FACILITY  => 1,
        self::TYPE_APARTMENT => 2,
        self::TYPE_REGION    => 3,
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

