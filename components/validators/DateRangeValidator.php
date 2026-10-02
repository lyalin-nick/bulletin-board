<?php

declare(strict_types=1);

namespace app\components\validators;

use app\helpers\DateRange;
use yii\validators\Validator;

class DateRangeValidator extends Validator
{
    public function init(): void
    {
        parent::init();

        $this->message ??= 'Укажите период в формате ДД.ММ.ГГГГ - ДД.ММ.ГГГГ.';
    }

    protected function validateValue($value): ?array
    {
        if (is_string($value) && DateRange::tryParse($value) !== null) {
            return null;
        }

        return [$this->message, []];
    }
}
