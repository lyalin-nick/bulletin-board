<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Category $model */

$this->title = 'Редактировать категорию: ' . $model->name;
$this->params['breadcrumbs'][] = 'Редактировать';
?>
<div class="category-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
