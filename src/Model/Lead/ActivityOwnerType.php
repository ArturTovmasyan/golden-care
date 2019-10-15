<?php

namespace App\Model\Lead;

class ActivityOwnerType
{
    const TYPE_LEAD = 1;
    const TYPE_REFERRAL = 2;
    const TYPE_ORGANIZATION = 3;
    const TYPE_OUTREACH = 4;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_LEAD => 'Lead',
        self::TYPE_REFERRAL => 'Referral',
        self::TYPE_ORGANIZATION => 'Organization',
        self::TYPE_OUTREACH => 'Outreach',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Lead' => '1',
        'Referral' => '2',
        'Organization' => '3',
        'Outreach' => '4',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_LEAD => 1,
        self::TYPE_REFERRAL => 2,
        self::TYPE_ORGANIZATION => 3,
        self::TYPE_OUTREACH => 4,
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

