<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
?>
<footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
        <?= Html::encode(Yii::$app->name) ?>
    </div>
    <strong>Панель модерации</strong> &copy; <?= date('Y') ?>
</footer>
