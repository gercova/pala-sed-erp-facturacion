<?php

namespace App\Services\Ebilling\Support;

use InvalidArgumentException;

class SunatServer
{
    public const PRODUCTION = '1';
    public const BETA = '3';

    public static function normalize(string|int|null $value): string
    {
        $normalized = trim((string) $value);

        if (in_array($normalized, [self::PRODUCTION, self::BETA], true)) {
            return $normalized;
        }

        throw new InvalidArgumentException('sunat_server debe ser 1 para produccion o 3 para beta.');
    }

    public static function environment(string|int|null $value): string
    {
        return self::normalize($value) === self::PRODUCTION ? 'production' : 'beta';
    }

    public static function label(string|int|null $value): string
    {
        return self::normalize($value) === self::PRODUCTION ? 'Produccion' : 'Beta';
    }
}
