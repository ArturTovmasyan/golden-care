<?php

namespace  App\Model;

class Assessment
{
    /**
     * Types
     */
    const TYPE_FILLED = 1;
    const TYPE_BLANK  = 2;

    /**
     * @var array
     */
    public static $types = [
        self::TYPE_FILLED => 'Filled',
        self::TYPE_BLANK  => 'Blank'
    ];
}

