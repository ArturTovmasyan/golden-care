<?php

namespace  App\Model;

class DiagnosisType
{
    /**
     * Types
     */
    const PRIMARY = 1;
    const SECONDARY = 2;
    const OTHER = 3;

    /**
     * @var array
     */
    private static $typeNames = [
        self::PRIMARY => 'Primary',
        self::SECONDARY => 'Secondary',
        self::OTHER => 'Other',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Primary' => '1',
        'Secondary' => '2',
        'Other' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::PRIMARY => 1,
        self::SECONDARY => 2,
        self::OTHER => 3,
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

