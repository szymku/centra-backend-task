<?php declare(strict_types=1);

namespace App;

class Utils
{
    public static function env(string $name, $default = null)
    {
        $value = getenv($name);
        if ($default !== null) {
            if (!empty($value)) {
                return $value;
            }
            return $default;
        }
        return (empty($value) && $default === null) ? die('Environment variable ' . $name . ' not found or has no value') : $value;
    }

    public static function hasValue(array $array, $key)
    {
        return array_key_exists($key, $array) && !empty($array[$key]);
    }

    public static function dump(...$data)
    {
        echo '<pre>';
        var_dump(...$data);
        echo '</pre>';
    }
}