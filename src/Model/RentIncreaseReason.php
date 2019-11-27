<?php

namespace  App\Model;

class RentIncreaseReason
{
    /**
     * Types
     */
    const ANNUAL = 1;
    const CARE_LEVEL_ADJUSTMENT = 2;

    /**
     * @var array
     */
    private static $typeNames = [
        self::ANNUAL => 'Annual',
        self::CARE_LEVEL_ADJUSTMENT => 'Care Level Adjustment',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Annual' => '1',
        'Care Level Adjustment' => '2',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::ANNUAL,
        self::CARE_LEVEL_ADJUSTMENT,
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

