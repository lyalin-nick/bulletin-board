<?php

declare(strict_types=1);

namespace app\modules\admin\widgets;

use app\helpers\DateRange;
use kartik\daterange\DateRangePicker;
use yii\helpers\ArrayHelper;

/**
 * Поле периода для панели фильтров. Формат и разделитель берутся из DateRange,
 * поэтому выбранный в пикере период гарантированно разберётся на сервере.
 *
 *   <?= $form->field($searchModel, 'created_range')->widget(DateRangeInput::class) ?>
 */
class DateRangeInput extends DateRangePicker
{
    /** Формат в pluginOptions задаётся в синтаксисе PHP и переводится в moment.js */
    public $convertFormat = true;

    /**
     * kartik ищет локали moment.js относительно файла класса виджета. У наследника это наш каталог,
     * и без этого свойства пикер молча остаётся на английском.
     */
    public $sourcePath = '@vendor/kartik-v/yii2-date-range/src';

    public function init(): void
    {
        $this->options += [
            'placeholder' => 'дд.мм.гггг - дд.мм.гггг',
            'autocomplete' => 'off',
        ];

        $this->pluginOptions = ArrayHelper::merge([
            'locale' => [
                'format' => DateRange::FORMAT,
                'separator' => DateRange::SEPARATOR,
                'cancelLabel' => 'Очистить',
            ],
        ], $this->pluginOptions);

        // Без этого убрать период из фильтра можно только стерев текст вручную
        $this->pluginEvents += [
            'cancel.daterangepicker' => "function () { jQuery(this).val('').trigger('change'); }",
        ];

        parent::init();
    }
}
