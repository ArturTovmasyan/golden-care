<?php

namespace App\Model;

class KeyFinanceType
{
    public const MONTHLY_BILLING_CUT_OFF_DATE = 1;
    public const BILLING_STATEMENTS_DELIVERY_DATE = 2;
    public const CALCULATE_RENT_DUE_DATE = 3;
    public const RENT_PAYMENT_DELINQUENT_DATE = 4;

    /**
     * @var array
     */
    private static $types = [
        self::MONTHLY_BILLING_CUT_OFF_DATE => 'Monthly Billing Cut Off Date',
        self::BILLING_STATEMENTS_DELIVERY_DATE => 'Billing Statements Delivery Date',
        self::CALCULATE_RENT_DUE_DATE => 'Calculate Rent Due Date',
        self::RENT_PAYMENT_DELINQUENT_DATE => 'Rent Payment Delinquent Date',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'Monthly Billing Cut Off Date' => '1',
        'Billing Statements Delivery Date' => '2',
        'Calculate Rent Due Date' => '3',
        'Rent Payment Delinquent Date' => '4',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::MONTHLY_BILLING_CUT_OFF_DATE => 1,
        self::BILLING_STATEMENTS_DELIVERY_DATE => 2,
        self::CALCULATE_RENT_DUE_DATE => 3,
        self::RENT_PAYMENT_DELINQUENT_DATE => 4,
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
}

