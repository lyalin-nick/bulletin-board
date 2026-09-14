<?php

/** @var yii\web\View $this */
/** @var app\modules\admin\forms\StaffForm $form */
/** @var app\models\Staff $model */

$this->title = 'Изменить сотрудника: ' . $model->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="staff-update">

    <?= $this->render('_form', [
        'form' => $form,
    ]) ?>

</div>
