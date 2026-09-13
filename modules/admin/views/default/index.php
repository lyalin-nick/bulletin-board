<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Дашборд';

$tiles = [
    ['label' => 'На модерации', 'icon' => 'bi-hourglass-split', 'bg' => 'text-bg-warning'],
    ['label' => 'Активные объявления', 'icon' => 'bi-megaphone', 'bg' => 'text-bg-success'],
    ['label' => 'Отклонённые', 'icon' => 'bi-x-octagon', 'bg' => 'text-bg-danger'],
];
?>

<div class="row">
    <?php foreach ($tiles as $tile): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="small-box <?= $tile['bg'] ?>">
                <div class="inner">
                    <h3>—</h3>
                    <p><?= Html::encode($tile['label']) ?></p>
                </div>
                <i class="small-box-icon bi <?= $tile['icon'] ?>"></i>
            </div>
        </div>
    <?php endforeach; ?>
</div>
