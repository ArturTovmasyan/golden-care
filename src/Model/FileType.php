<?php

namespace  App\Model;

class FileType
{
    const TYPE_DOCUMENT           = 1;
    const TYPE_RESIDENT_DOCUMENT  = 2;
    const TYPE_RESIDENT_INSURANCE = 3;
    const TYPE_RESIDENT_IMAGE     = 4;
    const TYPE_AVATAR             = 5;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_DOCUMENT  => 'Document',
        self::TYPE_RESIDENT_DOCUMENT => 'Resident Document',
        self::TYPE_RESIDENT_INSURANCE => 'Resident Insurance',
        self::TYPE_RESIDENT_IMAGE => 'Resident Image',
        self::TYPE_AVATAR => 'Avatar',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Document' => '1',
        'Resident Document' => '2',
        'Resident Insurance' => '3',
        'Resident Image' => '4',
        'Avatar' => '5',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_DOCUMENT  => 1,
        self::TYPE_RESIDENT_DOCUMENT => 2,
        self::TYPE_RESIDENT_INSURANCE => 3,
        self::TYPE_RESIDENT_IMAGE => 4,
        self::TYPE_AVATAR => 5,
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

