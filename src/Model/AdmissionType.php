<?php

namespace App\Model;

class AdmissionType
{
    const LONG_ADMIT = 1;
    const SHORT_ADMIT = 2;
    const READMIT = 3;
    const TEMPORARY_DISCHARGE = 4;
    const DISCHARGE = 5;

    /**
     * @var array
     */
    private static $types = [
        self::LONG_ADMIT => 'Long-Term Admit',
        self::SHORT_ADMIT => 'Short-Term Admit',
        self::READMIT => 'Re-admit',
        self::TEMPORARY_DISCHARGE => 'Temporary Discharge',
        self::DISCHARGE => 'Discharge',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Long-Term Admit' => '1',
        'Short-Term Admit' => '2',
        'Re-admit' => '3',
        'Temporary Discharge' => '4',
        'Discharge' => '5',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::LONG_ADMIT => 1,
        self::SHORT_ADMIT => 2,
        self::READMIT => 3,
        self::TEMPORARY_DISCHARGE => 4,
        self::DISCHARGE => 5,
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

