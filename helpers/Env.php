<?php

declare(strict_types=1);

namespace app\helpers;

use RuntimeException;

final class Env
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::read($key);

        if ($value === null) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }

    public static function required(string $key): string
    {
        $value = self::read($key);

        if ($value === null || $value === '') {
            throw new RuntimeException("Переменная окружения {$key} не задана или пуста. Проверьте .env");
        }

        return $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::read($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::read($key);

        return $value === null || !is_numeric($value) ? $default : (int) $value;
    }

    private static function read(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return null;
        }

        $value = (string) $value;

        if (strlen($value) > 1 && $value[0] === '"' && $value[-1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
