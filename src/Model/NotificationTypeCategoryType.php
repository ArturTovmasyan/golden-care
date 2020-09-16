<?php

namespace App\Model;

class NotificationTypeCategoryType
{
    const TYPE_SIXTY_DAYS_REPORT = 1;
    const TYPE_LEAD_ACTIVITY = 2;
    const TYPE_LEAD_CHANGE_LOG = 3;
    const TYPE_FACILITY_ACTIVITY = 4;
    const TYPE_CORPORATE_ACTIVITY = 5;
    const TYPE_RESIDENT_RENT_INCREASE = 6;
    const TYPE_DATABASE_USER_LOGIN_ACTIVITY = 7;
    const TYPE_LEAD_WEB_EMAIL = 8;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_SIXTY_DAYS_REPORT => 'Sixty Days Report',
        self::TYPE_LEAD_ACTIVITY => 'Lead Activity',
        self::TYPE_LEAD_CHANGE_LOG => 'Lead Change Log',
        self::TYPE_FACILITY_ACTIVITY => 'Facility Activity',
        self::TYPE_CORPORATE_ACTIVITY => 'Corporate Activity',
        self::TYPE_RESIDENT_RENT_INCREASE => 'Resident Rent Increase',
        self::TYPE_DATABASE_USER_LOGIN_ACTIVITY => 'Database User Login Activity',
        self::TYPE_LEAD_WEB_EMAIL => 'Lead Web Email',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Sixty Days Report' => '1',
        'Lead Activity' => '2',
        'Lead Change Log' => '3',
        'Facility Activity' => '4',
        'Corporate Activity' => '5',
        'Resident Rent Increase' => '6',
        'Database User Login Activity' => '7',
        'Lead Web Email' => '8',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_SIXTY_DAYS_REPORT => 1,
        self::TYPE_LEAD_ACTIVITY => 2,
        self::TYPE_LEAD_CHANGE_LOG => 3,
        self::TYPE_FACILITY_ACTIVITY => 4,
        self::TYPE_CORPORATE_ACTIVITY => 5,
        self::TYPE_RESIDENT_RENT_INCREASE => 6,
        self::TYPE_DATABASE_USER_LOGIN_ACTIVITY => 7,
        self::TYPE_LEAD_WEB_EMAIL => 8,
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

