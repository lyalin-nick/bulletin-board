<?php

use app\models\User;
use app\modules\admin\widgets\DateRangeInput;
use app\modules\admin\widgets\SearchPanel;

/** @var yii\web\View $this */
/** @var app\modules\admin\searches\UserSearch $model */
?>
<?php $form = SearchPanel::begin(['model' => $model]); ?>

<?= $form->field($model, 'name') ?>

<?= $form->field($model, 'surname') ?>

<?= $form->field($model, 'email') ?>

<?= $form->field($model, 'email_verified_range')->widget(DateRangeInput::class) ?>

<?= $form->field($model, 'phone') ?>

<?= $form->field($model, 'phone_verified_range')->widget(DateRangeInput::class) ?>

<?= $form->field($model, 'status')->dropDownList(User::$statusLabels, ['prompt' => 'Все']) ?>

<?= $form->field($model, 'created_range')->widget(DateRangeInput::class) ?>

<?php SearchPanel::end(); ?>
