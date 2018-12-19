<?php

namespace  App\Model;

class User
{
    public const GENDER_MALE   = 1;
    public const GENDER_FEMALE = 2;

    /**
     * @var array
     */
    public static $genders = [
        self::GENDER_MALE   => 'Male',
        self::GENDER_FEMALE => 'Female',
    ];

    /**
     * @var array
     */
    public static $genderValues = [
        self::GENDER_MALE,
        self::GENDER_FEMALE,
    ];

    /**
     * @return array
     */
    public static function completedValues()
    {
        return [
            'false' => 0,
            'true'  => 1,
        ];
    }

    /**
     * @return array
     */
    public static function enabledValues()
    {
        return [
            'false' => 0,
            'true'  => 1,
        ];
    }

    /**
     * @return array
     */
    public static function genderValues()
    {
        return [
            'Male'   => self::GENDER_MALE,
            'Female' => self::GENDER_FEMALE,
        ];
    }

    /**
     * @return array
     */
    public static function getGenderValues()
    {
        return self::$genderValues;
    }
}

