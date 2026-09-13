<?php

use app\models\Staff;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$staff = Yii::$app->user->identity;
$staffName = $staff instanceof Staff ? $staff->full_name : '';
?>
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">

        <ul class="navbar-nav align-items-center">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" data-lte-toggle="sidebar" href="#"
                   role="button" aria-label="Свернуть меню">
                    <i class="bi bi-list fs-5"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto align-items-center">

            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" href="#" id="theme-toggle"
                   data-bs-toggle="dropdown" aria-expanded="false" aria-label="Переключить тему">
                    <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
                    <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
                    <i class="bi bi-circle-half d-none" data-lte-theme-icon="auto"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="theme-toggle"
                    style="--bs-dropdown-min-width: 9rem">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="light">
                            <i class="bi bi-sun-fill me-2"></i>Светлая
                            <i class="bi bi-check-lg ms-auto d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="dark">
                            <i class="bi bi-moon-fill me-2"></i>Тёмная
                            <i class="bi bi-check-lg ms-auto d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center active"
                                data-bs-theme-value="auto">
                            <i class="bi bi-circle-half me-2"></i>Авто
                            <i class="bi bi-check-lg ms-auto d-none"></i>
                        </button>
                    </li>
                </ul>
            </li>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center"
                   data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4 lh-1"></i>
                    <span class="d-none d-md-inline ms-2"><?= Html::encode($staffName) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2">
                        <div class="fw-semibold"><?= Html::encode($staffName) ?></div>
                        <div class="small text-secondary">
                            <?= Html::encode($staff instanceof Staff ? $staff->email : '') ?>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= Html::beginForm(Url::to(['/admin/auth/logout']), 'post', ['class' => 'px-3 py-1']) ?>
                        <?= Html::submitButton(
                            '<i class="bi bi-box-arrow-right me-1"></i> Выйти',
                            ['class' => 'btn btn-sm btn-outline-danger w-100'],
                        ) ?>
                        <?= Html::endForm() ?>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
