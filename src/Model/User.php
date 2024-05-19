<?php

namespace  App\Model;

class User
{
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
}

