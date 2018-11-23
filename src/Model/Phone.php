<?php

namespace  App\Model;

class Phone
{
    /**
     * Compatible
     */
    const US_COMPATIBLE = 1;
    const WW_COMPATIBLE = 2;

    /**
     * Compatible
     */
    const TYPE_HOME      = 1;
    const TYPE_MOBILE    = 2;
    const TYPE_WORK      = 3;
    const TYPE_OFFICE    = 4;
    const TYPE_EMERGENCY = 5;
    const TYPE_FAX       = 6;
    const TYPE_ROOM      = 7;

    /**
     * @var array
     */
    private static $compatibleNames = [
        self::US_COMPATIBLE => 'US',
        self::WW_COMPATIBLE => 'WW',
    ];

    /**
     * @var array
     */
    private static $typeNames = [
        self::TYPE_HOME      => 'Home',
        self::TYPE_MOBILE    => 'Mobile',
        self::TYPE_WORK      => 'Work',
        self::TYPE_OFFICE    => 'Office',
        self::TYPE_EMERGENCY => 'Emergency',
        self::TYPE_FAX       => 'Fax',
        self::TYPE_ROOM      => 'Room'
    ];

    /**
     * @var array
     */
    private static $compatibleValues = [
        self::US_COMPATIBLE => '1',
        self::WW_COMPATIBLE => '2',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_HOME      => '1',
        self::TYPE_MOBILE    => '2',
        self::TYPE_WORK      => '3',
        self::TYPE_OFFICE    => '4',
        self::TYPE_EMERGENCY => '5',
        self::TYPE_FAX       => '6',
        self::TYPE_ROOM      => '7'
    ];

    /**
     * @return array
     */
    public static function getCompatibleValues()
    {
        return self::$compatibleValues;
    }

    /**
     * @return array
     */
    public static function getTypeValues()
    {
        return self::$typeValues;
    }
}

