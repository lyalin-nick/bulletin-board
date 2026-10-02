<?php

declare(strict_types=1);

namespace app\helpers;

use DateTimeImmutable;

/**
 * Период из целых дней в формате фильтров админки: «01.10.2026 - 05.10.2026».
 * Даты разбираются в часовом поясе приложения (UTC) — в нём же они хранятся в БД.
 */
final class DateRange
{
    public const string FORMAT = 'd.m.Y';
    public const string SEPARATOR = ' - ';

    private function __construct(
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to,
    ) {
    }

    /**
     * Разбор строки периода
     * @param string $value
     * @return self|null null, если строка не является корректным периодом
     */
    public static function tryParse(string $value): ?self
    {
        $parts = explode(self::SEPARATOR, trim($value));

        if (count($parts) !== 2) {
            return null;
        }

        $from = self::parseDate(trim($parts[0]));
        $to = self::parseDate(trim($parts[1]));

        if ($from === null || $to === null) {
            return null;
        }

        return $from <= $to ? new self($from, $to) : new self($to, $from);
    }

    /**
     * Условие для andFilterWhere(): для пустого значения — [], то есть без фильтра.
     * Корректность значения проверяет DateRangeValidator, здесь некорректное тоже даёт [].
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public static function condition(string $column, mixed $value): array
    {
        $range = is_string($value) ? self::tryParse($value) : null;

        if ($range === null) {
            return [];
        }

        return [
            'and',
            ['>=', $column, $range->from->format('Y-m-d H:i:s')],
            ['<', $column, $range->to->modify('+1 day')->format('Y-m-d H:i:s')],
        ];
    }

    private static function parseDate(string $value): ?DateTimeImmutable
    {
        // «!» обнуляет время, иначе createFromFormat подставит текущее
        $date = DateTimeImmutable::createFromFormat('!' . self::FORMAT, $value);
        $errors = DateTimeImmutable::getLastErrors();

        if ($date === false || ($errors !== false && $errors['warning_count'] > 0)) {
            return null;
        }

        return $date;
    }
}
