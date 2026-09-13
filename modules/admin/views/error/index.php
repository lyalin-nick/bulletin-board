<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\HttpException;

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Throwable|null $exception */

$this->title = $name;

$status = $exception instanceof HttpException ? $exception->statusCode : 500;
?>

<div class="card">
    <div class="card-body login-card-body text-center">

        <h1 class="display-4 fw-bold mb-1 <?= $status < 500 ? 'text-warning' : 'text-danger' ?>">
            <?= Html::encode((string) $status) ?>
        </h1>

        <p class="fs-5 mb-3"><?= Html::encode($name) ?></p>

        <p class="text-secondary"><?= nl2br(Html::encode($message)) ?></p>

        <div class="d-grid gap-2 mt-4">
            <?= Html::a(
                '<i class="bi bi-house me-1"></i> На главную',
                Url::to(['/admin/default/index']),
                ['class' => 'btn btn-primary'],
            ) ?>
        </div>

    </div>
</div>
