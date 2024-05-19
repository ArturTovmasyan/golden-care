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
            'false' => 0,
            'true'  => 1,
        ];
    }
}

