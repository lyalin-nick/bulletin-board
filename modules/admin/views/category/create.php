<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Category $model */

$this->title = 'Добавить категорию';
//$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
