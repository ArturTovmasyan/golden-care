<?php

namespace  App\Model;

class AdmissionType
{
    const ADMIT               = 1;
    const READMIT             = 2;
    const TEMPORARY_DISCHARGE = 3;
    const DISCHARGE           = 4;

    /**
     * @var array
     */
    private static $types = [
        self::ADMIT               => 'Admit',
        self::READMIT             => 'Re-admit',
        self::TEMPORARY_DISCHARGE => 'Temporary Discharge',
        self::DISCHARGE           => 'Discharge',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Admit'               => '1',
        'Re-admit'            => '2',
        'Temporary Discharge' => '3',
        'Discharge'           => '4',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::ADMIT               => 1,
        self::READMIT             => 2,
        self::TEMPORARY_DISCHARGE => 3,
        self::DISCHARGE           => 4,
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

