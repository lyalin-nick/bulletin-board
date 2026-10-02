<?php

declare(strict_types=1);

namespace app\modules\admin\widgets;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Json;

/**
 * Панель фильтров над гридом, единая для всех разделов админки:
 * сворачиваемая карточка, поля сеткой, «Найти» / «Сбросить», счётчик активных фильтров.
 *
 *   <?php $form = SearchPanel::begin(['model' => $searchModel]); ?>
 *       <?= $form->field($searchModel, 'email') ?>
 *       <?= $form->field($searchModel, 'status')->dropDownList($statuses, ['prompt' => 'Все']) ?>
 *   <?php SearchPanel::end(); ?>
 */
class SearchPanel extends ActiveForm
{
    public ?Model $model = null;

    /**
     * Куда ведёт «Сбросить». Html::resetButton из шаблона Gii для этого не годится:
     * он возвращает поля к значениям при загрузке страницы, то есть к текущим
     * фильтрам, и при активном поиске не делает ничего.
     */
    public array|string $resetUrl = ['index'];

    /**
     * Атрибуты-контекст: уходят скрытыми полями и не считаются фильтрами.
     * Пример — parent_id в категориях: поиск идёт внутри открытого уровня.
     */
    public array $contextAttributes = [];

    public string $title = 'Фильтры';

    /** Полей в ряд на широком экране; должно делить 12 нацело. */
    public int $columns = 4;

    public $action = ['index'];
    public $method = 'get';
    public $enableClientScript = false;

    public function init(): void
    {
        if ($this->model === null) {
            throw new InvalidConfigException('SearchPanel::$model обязателен.');
        }

        if (!in_array($this->columns, [1, 2, 3, 4, 6], true)) {
            throw new InvalidConfigException('SearchPanel::$columns: допустимо 1, 2, 3, 4 или 6.');
        }

        $this->fieldConfig = array_replace_recursive([
            'options' => ['class' => 'col-12 col-md-6 col-lg-' . (12 / $this->columns)],
        ], $this->fieldConfig);

        // ActiveForm::init() открывает буфер вывода: всё, что отрендерено между
        // begin() и end(), он заберёт в run() и обернёт тегом <form>.
        parent::init();

        echo Html::beginTag('div', ['class' => 'row g-3 align-items-end']);
    }

    public function run(): string
    {
        foreach ($this->contextAttributes as $attribute) {
            echo Html::activeHiddenInput($this->model, $attribute);
        }

        echo Html::tag(
            'div',
            Html::submitButton('<i class="bi bi-search"></i> Найти', ['class' => 'btn btn-primary'])
            . Html::a('<i class="bi bi-x-lg"></i> Сбросить', $this->resetUrl, ['class' => 'btn btn-outline-secondary']),
            ['class' => 'col-12 d-flex gap-2'],
        );

        echo Html::endTag('div');

        $this->registerCleanUrlScript();

        return $this->renderCard(parent::run());
    }

    private function renderCard(string $form): string
    {
        $active = $this->countActiveFilters();
        $bodyId = $this->options['id'] . '-body';

        $badge = $active > 0
            ? ' ' . Html::tag('span', (string) $active, ['class' => 'badge text-bg-primary ms-1', 'title' => 'Активных фильтров'])
            : '';

        $header = Html::tag(
            'div',
            Html::a('<i class="bi bi-funnel"></i> ' . Html::encode($this->title) . $badge, '#' . $bodyId, [
                'class' => 'card-title text-decoration-none text-reset',
                'data-bs-toggle' => 'collapse',
                'role' => 'button',
                'aria-expanded' => $active > 0 ? 'true' : 'false',
                'aria-controls' => $bodyId,
            ]),
            ['class' => 'card-header'],
        );

        // Без активных фильтров панель свёрнута, чтобы не отнимать экран у грида;
        // с ними — раскрыта, иначе непонятно, почему в списке не все записи.
        $body = Html::tag('div', Html::tag('div', $form, ['class' => 'card-body']), [
            'id' => $bodyId,
            'class' => 'collapse' . ($active > 0 ? ' show' : ''),
        ]);

        return Html::tag('div', $header . $body, ['class' => 'card card-outline card-secondary mb-3']);
    }

    private function countActiveFilters(): int
    {
        $count = 0;

        foreach ($this->model->safeAttributes() as $attribute) {
            if (in_array($attribute, $this->contextAttributes, true)) {
                continue;
            }

            $value = $this->model->$attribute;

            if ($value !== null && $value !== '' && $value !== []) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Пустые поля перед отправкой отключаются, и в URL остаются только реально заданные фильтры.
     */
    private function registerCleanUrlScript(): void
    {
        $id = Json::htmlEncode($this->options['id']);

        $this->getView()->registerJs(<<<JS
            (function () {
                const form = document.getElementById({$id});
                if (!form) {
                    return;
                }
                form.addEventListener('submit', function () {
                    form.querySelectorAll('input, select, textarea').forEach(function (el) {
                        if (el.name && el.value === '') {
                            el.disabled = true;
                        }
                    });
                });
                // Возврат кнопкой «Назад» может отдать страницу из bfcache с отключёнными полями.
                window.addEventListener('pageshow', function () {
                    form.querySelectorAll(':disabled').forEach(function (el) {
                        el.disabled = false;
                    });
                });
            })();
            JS);
    }
}
