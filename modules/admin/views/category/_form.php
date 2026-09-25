<?php

use app\models\Category;
use app\modules\admin\widgets\SlugGenerator;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Category $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'parent_id')->dropDownList(Category::getParents($model->id), ['prompt' => 'Нет'])->label('Родительская категория') ?>

    <div class="row">
        <div class="col">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col">
            <?= SlugGenerator::widget([
                'form' => $form,
                'model' => $model,
            ]) ?>
        </div>
    </div>

    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'is_active')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
