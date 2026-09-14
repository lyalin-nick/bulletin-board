<?php

/** @var yii\web\View $this */
/** @var app\modules\admin\forms\StaffForm $form */

$this->title = 'Добавить сотрудника';
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="staff-create">

    <?= $this->render('_form', [
        'form' => $form,
    ]) ?>

</div>
