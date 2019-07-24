<?php

namespace App\Util;


class StringUtil
{
    /**
     * Convert a value to title case.
     *
     * @param  string $value
     * @return string
     */
    public static function title_case($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string $value
     * @return string
     */
    public static function studly_case($value)
    {
        static $studlyCache = [];
        $key = $value;
        if (isset($studlyCache[$key])) {
            return $studlyCache[$key];
        }
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));
        return $studlyCache[$key] = str_replace(' ', '', $value);
    }

    public static function kebab_case($value)
    {
        return self::snake_case($value, '-');
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string $value
     * @param  string $delimiter
     * @return string
     */
    public static function snake_case($value, $delimiter = '_')
    {
        static $snakeCache = [];
        $key = $value . $delimiter;
        if (isset($snakeCache[$key])) {
            return $snakeCache[$key];
        }
        if (!ctype_lower($value)) {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $value));
        }
        return $snakeCache[$key] = $value;
    }

    /**
     * Convert a value to camel case.
     *
     * @param  string $value
     * @return string
     */
    public static function camel_case($value)
    {
        static $camelCache = [];
        if (isset($camelCache[$value])) {
            return $camelCache[$value];
        }
        return $camelCache[$value] = lcfirst(self::studly_case($value));
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function starts_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) return true;
        }
        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function ends_with($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === substr($haystack, -strlen($needle))) return true;
        }
        return false;
    }

    public static function slugify($text)
    {
        if (empty($text)) {
            return 'n-a';
        }

        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        return $text;
    }
}
