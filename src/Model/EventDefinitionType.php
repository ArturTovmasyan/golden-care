<?php

namespace  App\Model;

class EventDefinitionType
{
    /**
     * Types
     */
    const NONE = 1;
    const ABSENCE = 2;
    const ASSESSMENT = 3;

    /**
     * @var array
     */
    private static $typeNames = [
        self::NONE => 'None',
        self::ABSENCE => 'Absence',
        self::ASSESSMENT => 'Assessment',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'None' => '1',
        'Absence' => '2',
        'Assessment' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::NONE,
        self::ABSENCE,
        self::ASSESSMENT,
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

