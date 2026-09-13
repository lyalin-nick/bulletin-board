<?php

use app\modules\admin\forms\LoginForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var LoginForm $model */

$this->title = 'Вход в панель модерации';
?>

<h1 class="login-logo">
    <a href="#" class="text-decoration-none"><b>Bulletin</b> Board</a>
</h1>

<div class="card">
    <div class="card-body login-card-body">
        <p class="login-box-msg">Введите учётные данные сотрудника</p>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'enableClientValidation' => false,
        ]); ?>

        <?= $form->field($model, 'email', ['options' => ['class' => 'mb-3']])
            ->textInput([
                'type' => 'email',
                'autofocus' => true,
                'autocomplete' => 'username',
                'placeholder' => 'staff@example.com',
            ]) ?>

        <?= $form->field($model, 'password', ['options' => ['class' => 'mb-3']])
            ->passwordInput([
                'autocomplete' => 'current-password',
                'placeholder' => '••••••••',
            ]) ?>

        <div class="d-grid">
            <?= Html::submitButton(
                '<i class="bi bi-box-arrow-in-right me-1"></i> Войти',
                ['class' => 'btn btn-primary', 'name' => 'login-button'],
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
