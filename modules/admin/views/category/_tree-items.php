<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int|null $parentId */
/** @var array $byParent */
?>
<ul class="category-tree" data-parent="<?= $parentId ?>">
    <?php foreach ($byParent[$parentId] ?? [] as $c): ?>
        <li data-id="<?= $c->id ?>">
<!--            <span class="drag-handle bi bi-grip-vertical"></span>-->
            <?= Html::encode($c->name) ?>
            <?= $c->is_active ? '' : '<span class="badge text-bg-secondary">скрыта</span>' ?>
<!--            <span class="actions">--><?php //= Html::a('изм.', ['update', 'id' => $c->id]) ?><!--</span>-->
            <?= $this->render('_tree-items', ['byParent' => $byParent, 'parentId' => $c->id]) ?>
        </li>
    <?php endforeach; ?>
</ul>
