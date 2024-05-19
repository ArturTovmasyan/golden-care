<?php
namespace App\Api\V1\Service;


/**
 * Class UserService
 * @package App\Api\V1\Service
 */
class UserService
{
    /**
     * @param int $length
     * @return bool|string
     */
    public function generatePassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";

        return substr(str_shuffle( $chars ), 0, $length);
    }
}