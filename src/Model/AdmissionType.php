<?php

namespace App\Model;

class AdmissionType
{
    public const LONG_ADMIT = 1;
    public const SHORT_ADMIT = 2;
    public const READMIT = 3;
    public const TEMPORARY_DISCHARGE = 4;
    public const PENDING_DISCHARGE = 5;
    public const DISCHARGE = 6;

    //Assisted Living (AL) - Facility
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
    public static function getTypes(): array
    {
        return self::$types;
    }

    /**
     * @return array
     */
    public static function getTypeDefaultNames(): array
    {
        return self::$typeDefaultNames;
    }

    /**
     * @return array
     */
    public static function getTypeValues(): array
    {
        return self::$typeValues;
    }

    //Independent Living (IL) - Apartment
    /**
     * @var array
     */
    private static $apartmentTypes = [
        self::LONG_ADMIT => 'Long-Term Rental',
        self::SHORT_ADMIT => 'Short-Term Rental',
        self::READMIT => 'Re-admit',
        self::PENDING_DISCHARGE => 'Notice to Vacate',
        self::DISCHARGE => 'Move Out',
    ];

    /**
     * @var array
     */
    private static $apartmentTypeDefaultNames = [
        'Long-Term Rental' => '1',
        'Short-Term Rental' => '2',
        'Re-admit' => '3',
        'Notice to Vacate' => '5',
        'Move Out' => '6',
    ];

    /**
     * @var array
     */
    private static $apartmentTypeValues = [
        self::LONG_ADMIT => 1,
        self::SHORT_ADMIT => 2,
        self::READMIT => 3,
        self::PENDING_DISCHARGE => 5,
        self::DISCHARGE => 6,
    ];

    /**
     * @return array
     */
    public static function getApartmentTypes(): array
    {
        return self::$apartmentTypes;
    }

    /**
     * @return array
     */
    public static function getApartmentTypeDefaultNames(): array
    {
        return self::$apartmentTypeDefaultNames;
    }

    /**
     * @return array
     */
    public static function getApartmentTypeValues(): array
    {
        return self::$apartmentTypeValues;
    }
}

