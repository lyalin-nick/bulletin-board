<?php

use app\models\Staff;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\admin\forms\StaffForm $form */
/** @var yii\widgets\ActiveForm $activeForm */
?>

<div class="staff-form">

    <?php $activeForm = ActiveForm::begin(); ?>

    <?= $activeForm->field($form, 'full_name')->textInput(['maxlength' => true]) ?>

    <?= $activeForm->field($form, 'email')->textInput(['maxlength' => true]) ?>

    <?= $activeForm->field($form, 'status')->dropDownList(Staff::$statusLabels) ?>

    <?= $activeForm->field($form, 'role')->dropDownList(ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', fn($r) => $r->description ?: $r->name)) ?>

    <?= $activeForm->field($form, 'password')->passwordInput(['autocomplete' => 'new-password'])
        ->hint($form->scenario === $form::SCENARIO_UPDATE ? 'Оставьте пустым, если не нужно менять пароль.' : null) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
