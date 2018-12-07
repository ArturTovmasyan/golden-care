<?php

namespace  App\Model;

class ContractState
{
    /**
     * Types
     */
    const ACTIVE = 1;
    const SUSPENDED = 2;
    const TERMINATED = 3;

    /**
     * @var array
     */
    private static $typeNames = [
        self::ACTIVE => 'Active',
        self::SUSPENDED => 'Suspended',
        self::TERMINATED => 'Terminated',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Active' => '1',
        'Suspended' => '2',
        'Terminated' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::ACTIVE,
        self::SUSPENDED,
        self::TERMINATED,
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

