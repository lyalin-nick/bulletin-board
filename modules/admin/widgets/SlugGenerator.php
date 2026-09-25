<?php

namespace app\modules\admin\widgets;

use app\modules\admin\assets\SlugGeneratorAsset;
use Exception;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\BaseHtml;
use yii\widgets\ActiveForm;

class SlugGenerator extends Widget
{
    public ActiveForm $form;

    public ActiveRecord $model;

    public string $attribute = 'name';
    public string $slugAttribute = 'slug';

    public array $options = ['maxlength' => true];

    public function init(): void
    {
        parent::init();

        if (!$this->model->hasAttribute($this->attribute)) {
            throw new Exception('У модели отсутствует поле ' . $this->attribute, 422);
        }
        if (!$this->model->hasAttribute($this->slugAttribute)) {
            throw new Exception('У модели отсутствует поле ' . $this->slugAttribute, 422);
        }
    }

    public function run(): void
    {
        $tooltipTitle = 'ЧПУ сгенерируется из поля «' . $this->model->getAttributeLabel($this->attribute) . '»';
        $dataSlugInput = BaseHtml::getInputName($this->model, $this->slugAttribute);
        $dataSluggableInput = BaseHtml::getInputName($this->model, $this->attribute);

        echo $this->form->field(
            $this->model,
            $this->slugAttribute,
            [
                'template' => "{label}\n" .
                    "<div class='input-group'>" .
                    "<span class='input-group-prepend'>" .
                    "<button data-bs-toggle='tooltip' title='{$tooltipTitle}' type='button' class='btn btn-primary js-generate-surl' data-slug-input='{$dataSlugInput}' data-sluggable-input='{$dataSluggableInput}'><i class='bi bi-magic'></i></button>" .
                    '</span>' .
                    '{input}' .
                    '</div>' .
                    "\n{hint}" .
                    "\n{error}"
            ]
        )
            ->textInput($this->options);

        $view = $this->getView();

        SlugGeneratorAsset::register($view);
    }
}
