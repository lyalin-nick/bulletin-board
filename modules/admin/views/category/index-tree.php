<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $byParent */
/** @var int $parentId */

$this->title = 'Структура категорий';
$this->params['breadcrumbs'][] = 'Структура';
?>
<div class="category-index-tree">
    <p>
        <?= Html::a('<i class="bi bi-arrow-left"></i> Назад', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= $this->render('_tree-items', ['byParent' => $byParent, 'parentId' => $parentId]); ?>
</div>
