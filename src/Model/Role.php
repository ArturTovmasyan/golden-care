<?php

namespace  App\Model;

class Role
{
    /**
     * @return array
     */
    public static function defaultValues()
    {
        return [
            'not'     => 0,
            'default' => 1,
        ];
    }
}

