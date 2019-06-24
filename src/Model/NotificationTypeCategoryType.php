<?php

namespace  App\Model;

class NotificationTypeCategoryType
{
    const TYPE_SIXTY_DAYS_REPORT = 1;
    const TYPE_LEAD_ACTIVITY     = 2;
    const TYPE_LEAD_CHANGE_LOG   = 3;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_SIXTY_DAYS_REPORT => 'Sixty Days Report',
        self::TYPE_LEAD_ACTIVITY => 'Lead Activity',
        self::TYPE_LEAD_CHANGE_LOG => 'Lead Change Log',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Sixty Days Report' => '1',
        'Lead Activity' => '2',
        'Lead Change Log' => '3',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_SIXTY_DAYS_REPORT => 1,
        self::TYPE_LEAD_ACTIVITY => 2,
        self::TYPE_LEAD_CHANGE_LOG => 3,
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
