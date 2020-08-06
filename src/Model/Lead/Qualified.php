<?php

namespace App\Model\Lead;

class Qualified
{
    const TYPE_YES = 1;
    const TYPE_NOT_SURE = 2;
    const TYPE_NO = 3;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_YES => 'Yes',
        self::TYPE_NOT_SURE => '??',
        self::TYPE_NO => 'No',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Yes' => '1',
        '??' => '2',
        'No' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_YES => 1,
        self::TYPE_NOT_SURE => 2,
        self::TYPE_NO => 3,
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

