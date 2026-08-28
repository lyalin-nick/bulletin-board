<?php

declare(strict_types=1);

namespace app\helpers;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Yii;

final class Phone
{
    /**
     * Нормализация номера к стандарту E.164
     * @param string $value
     * @param string|null $region
     * @return string|null
     */
    public static function normalize(string $value, ?string $region = null): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $number = $util->parse($value, $region ?? (Yii::$app->params['phoneRegion'] ?? 'RU'));
        } catch (NumberParseException) {
            return null;
        }

        if (!$util->isValidNumber($number)) {
            return null;
        }

        return $util->format($number, PhoneNumberFormat::E164);
    }

    /**
     * Проверка строки на наличие номера телефона
     * @param string $value
     * @return bool
     */
    public static function looksLikePhone(string $value): bool
    {
        return preg_match('/^[\d\s\-()+.]{5,25}$/u', trim($value)) === 1;
    }
}
