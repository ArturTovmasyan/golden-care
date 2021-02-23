<?php

namespace App\Model;

class Report
{
    /**
     * Compatible
     */
    const FORMAT_PDF = 'pdf';
    const FORMAT_CSV = 'csv';
    const FORMAT_XLS = 'xls';
    const FORMAT_TXT = 'txt';
    /**
     * @var array
     */
    public static $formats = [
        self::FORMAT_PDF,
        self::FORMAT_CSV,
        self::FORMAT_XLS,
        self::FORMAT_TXT,
    ];

    /**
     * @return array
     */
    public static function getFormats()
    {
        return self::$formats;
    }
}

