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
    /**
     * @var array
     */
    public static $formats = [
        self::FORMAT_PDF,
        self::FORMAT_CSV,
        self::FORMAT_XLS,
    ];

    /**
     * @return array
     */
    public static function getFormats()
    {
        return self::$formats;
    }
}

