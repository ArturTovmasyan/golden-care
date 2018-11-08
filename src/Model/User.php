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
            'not completed' => 0,
            'completed'     => 1,
        ];
    }

    /**
     * @return array
     */
    public static function enabledValues()
    {
        return [
            'disabled' => 0,
            'enabled'  => 1,
        ];
    }
}

