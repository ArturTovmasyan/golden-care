<?php

namespace App\Model;

class Boolean
{
    /**
     * @return array
     */
    public static function defaultValues()
    {
        return [
            'false' => 0,
            'true' => 1,
        ];
    }
}

