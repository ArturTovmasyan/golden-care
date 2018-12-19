<?php

namespace  App\Model;

class Resident
{
    /**
     * States
     */
    const ACTIVE   = 1;
    const INACTIVE = 2;

    const TYPE_FACILITY  = 1;
    const TYPE_APARTMENT = 2;
    const TYPE_REGION    = 3;

    /**
     * @var array
     */
    public static $stateNames = [
        self::ACTIVE   => 'Active',
        self::INACTIVE => 'Inactive',
    ];

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
    private static $typeValues = [
        self::TYPE_FACILITY,
        self::TYPE_APARTMENT,
        self::TYPE_REGION,
    ];

    /**
     * @return array
     */
    public static function getStateNames()
    {
        return self::$stateNames;
    }

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
    public static function getTypeValues()
    {
        return self::$typeValues;
    }
}

