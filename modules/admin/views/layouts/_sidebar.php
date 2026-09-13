<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$items = [
    [
        'label' => 'Дашборд',
        'icon' => 'bi-speedometer2',
        'url' => ['/admin/default/index'],
        'route' => 'admin/default',
    ],
//    [
//        'label' => 'Категории',
//        'icon' => 'bi-diagram-3',
//        'url' => ['/admin/category/index'],
//        'route' => 'admin/category',
//        'permission' => 'manageCategories',
//        'enabled' => false,
//    ],
//    [
//        'label' => 'Админы',
//        'icon' => 'bi-people',
//        'url' => ['/admin/staff/index'],
//        'route' => 'admin/staff',
//        'permission' => 'manageStaff',
//        'enabled' => false,
//    ],
];
$currentRoute = Yii::$app->controller->getRoute();

$isVisible = static function (array $item): bool {
    if (!isset($item['permission'])) {
        return true;
    }

    return Yii::$app->user->can($item['permission']);
};
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="<?= Url::to(['/admin/default/index']) ?>" class="brand-link">
            <i class="bi bi-clipboard-check brand-image opacity-75 ms-3 me-2"></i>
            <span class="brand-text fw-light">Панель модерации</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Основная навигация">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">
                <?php foreach ($items as $item): ?>
                    <?php if (isset($item['header'])): ?>
                        <li class="nav-header"><?= Html::encode(mb_strtoupper($item['header'])) ?></li>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if (!$isVisible($item)): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php
                    $enabled = $item['enabled'] ?? true;
                    $active = str_starts_with($currentRoute, $item['route']);
                    $class = 'nav-link' . ($active ? ' active' : '') . ($enabled ? '' : ' disabled text-secondary');
                    ?>
                    <li class="nav-item">
                        <a href="<?= $enabled ? Url::to($item['url']) : '#' ?>" class="<?= $class ?>"
                            <?= $enabled ? '' : 'aria-disabled="true" tabindex="-1"' ?>>
                            <i class="nav-icon bi <?= $item['icon'] ?>"></i>
                            <p>
                                <?= Html::encode($item['label']) ?>
                                <?php if (!$enabled): ?>
                                    <span class="nav-badge badge text-bg-secondary me-3">скоро</span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
