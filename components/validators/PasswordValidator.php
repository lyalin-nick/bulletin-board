<?php

declare(strict_types=1);

namespace app\components\validators;

use yii\validators\Validator;

class PasswordValidator extends Validator
{
    /**
     * bcrypt обрезает пароль на 72 байтах
     */
    public const int BCRYPT_MAX_BYTES = 72;

    public int $min = 8;
    public int $max = 64;
    public bool $lower = false;
    public bool $upper = false;
    public bool $digit = false;
    public bool $special = false;
    public bool $forbidEdgeWhitespace = true;

    public function validateAttribute($model, $attribute): void
    {
        foreach ($this->violations($model->$attribute) as [$message, $params]) {
            $this->addError($model, $attribute, $message, $params);
        }
    }

    protected function validateValue($value): ?array
    {
        $violations = $this->violations($value);

        return $violations === [] ? null : $violations[0];
    }

    /**
     * @return array<array{0: string, 1: array}>
     */
    private function violations(mixed $value): array
    {
        if (!is_string($value)) {
            return [['Пароль должен быть строкой.', []]];
        }

        $violations = [];
        $length = mb_strlen($value);

        if ($length < $this->min) {
            $violations[] = ['Пароль должен быть не короче {min} символов.', ['min' => $this->min]];
        }

        if ($length > $this->max) {
            $violations[] = ['Пароль должен быть не длиннее {max} символов.', ['max' => $this->max]];
        }

        if (strlen($value) > self::BCRYPT_MAX_BYTES) {
            $violations[] = [
                'Пароль не должен превышать {bytes} байт.',
                ['bytes' => self::BCRYPT_MAX_BYTES],
            ];
        }

        if ($this->forbidEdgeWhitespace && preg_match('/^\s|\s$/u', $value) === 1) {
            $violations[] = ['Пароль не должен начинаться или заканчиваться пробелом.', []];
        }

        // \p{Ll} и \p{Lu} работают и с кириллицей: «пароль» и «ПАРОЛЬ» засчитываются.
        if ($this->lower && preg_match('/\p{Ll}/u', $value) !== 1) {
            $violations[] = ['Пароль должен содержать строчную букву.', []];
        }

        if ($this->upper && preg_match('/\p{Lu}/u', $value) !== 1) {
            $violations[] = ['Пароль должен содержать заглавную букву.', []];
        }

        if ($this->digit && preg_match('/\p{Nd}/u', $value) !== 1) {
            $violations[] = ['Пароль должен содержать цифру.', []];
        }

        if ($this->special && preg_match('/[^\p{L}\p{N}\s]/u', $value) !== 1) {
            $violations[] = ['Пароль должен содержать специальный символ.', []];
        }

        return $violations;
    }
}
