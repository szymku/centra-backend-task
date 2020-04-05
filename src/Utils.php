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

        if (empty($value) && $default === null) {
            throw new \RuntimeException('Environment variable ' . $name . ' not found or has no value');
        }

        return $value;
    }

    public static function hasValue(array $array, $key): bool
    {
        return array_key_exists($key, $array) && !empty($array[$key]);
    }

    public static function dump(...$data)
    {
        echo '<pre>';
        var_dump(...$data);
        echo '</pre>';
    }

    public static function dumpAndExit(...$data)
    {
        echo '<pre>';
        var_dump(...$data);
        echo '</pre>';
        exit;
    }

    public static function logError(string $path, \Throwable $e)
    {
        $log = [
            'date' => date(DATE_ATOM),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        file_put_contents($path, json_encode($log) . PHP_EOL, FILE_APPEND);
    }
}