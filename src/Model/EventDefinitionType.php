<?php

namespace  App\Model;

class EventDefinitionType
{
    /**
     * Types
     */
    const NONE = 1;
    const ABSENCE = 2;

    /**
     * @var array
     */
    private static $typeNames = [
        self::NONE => 'None',
        self::ABSENCE => 'Absence',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'None' => '1',
        'Absence' => '2',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::NONE,
        self::ABSENCE,
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

