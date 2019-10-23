<?php

namespace App\Model\Lead;

class ActivityOwnerType
{
    const TYPE_LEAD = 1;
    const TYPE_REFERRAL = 2;
    const TYPE_ORGANIZATION = 3;
    const TYPE_OUTREACH = 4;
    const TYPE_CONTACT = 5;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_LEAD => 'Lead',
        self::TYPE_REFERRAL => 'Referral',
        self::TYPE_ORGANIZATION => 'Organization',
        self::TYPE_OUTREACH => 'Outreach',
        self::TYPE_CONTACT => 'Contact',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Lead' => '1',
        'Referral' => '2',
        'Organization' => '3',
        'Outreach' => '4',
        'Contact' => '5',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_LEAD => 1,
        self::TYPE_REFERRAL => 2,
        self::TYPE_ORGANIZATION => 3,
        self::TYPE_OUTREACH => 4,
        self::TYPE_CONTACT => 5,
    ];

    /**
     * @var array
     */
    private static $values = [
        self::TYPE_LEAD,
        self::TYPE_REFERRAL,
        self::TYPE_ORGANIZATION,
        self::TYPE_OUTREACH,
        self::TYPE_CONTACT,
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

    /**
     * @return array
     */
    public static function getValues()
    {
        return self::$values;
    }
}

