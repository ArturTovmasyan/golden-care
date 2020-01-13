<?php

namespace App\Model;

class EventDefinitionView
{
    /**
     * Types
     */
    const RESIDENT = 1;
    const FACILITY = 2;
    const CORPORATE = 3;

    /**
     * @var array
     */
    private static $typeNames = [
        self::RESIDENT => 'Resident',
        self::FACILITY => 'Facility',
        self::CORPORATE => 'Corporate',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Resident' => '1',
        'Facility' => '2',
        'Corporate' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::RESIDENT,
        self::FACILITY,
        self::CORPORATE,
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

