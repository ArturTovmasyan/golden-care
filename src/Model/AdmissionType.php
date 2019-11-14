<?php

namespace App\Model;

class AdmissionType
{
    const LONG_ADMIT = 1;
    const SHORT_ADMIT = 2;
    const READMIT = 3;
    const TEMPORARY_DISCHARGE = 4;
    const PENDING_DISCHARGE = 5;
    const DISCHARGE = 6;

    /**
     * @var array
     */
    private static $types = [
        self::LONG_ADMIT => 'Long-Term Admit',
        self::SHORT_ADMIT => 'Short-Term Admit',
        self::READMIT => 'Re-admit',
        self::TEMPORARY_DISCHARGE => 'Temporary Discharge',
        self::PENDING_DISCHARGE => 'Pending Discharge',
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
        'Pending Discharge' => '5',
        'Discharge' => '6',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::LONG_ADMIT => 1,
        self::SHORT_ADMIT => 2,
        self::READMIT => 3,
        self::TEMPORARY_DISCHARGE => 4,
        self::PENDING_DISCHARGE => 5,
        self::DISCHARGE => 6,
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

