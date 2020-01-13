<?php

namespace App\Model;

class Report
{
    /**
     * Compatible
     */
    const FORMAT_PDF = 1;
    const FORMAT_CSV = 2;
    /**
     * @var array
     */
    public static $formats = [
        self::FORMAT_PDF,
        self::FORMAT_CSV,
    ];

    /**
     * @return array
     */
    public static function getFormats()
    {
        return self::$formats;
    }
}

