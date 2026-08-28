<?php

declare(strict_types=1);

use app\models\user\ConfirmCode;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var string $purpose */
/** @var string $code */

?>
<div class="verify-email">
    <p>Здравствуйте, <?= Html::encode($user->fullName) ?>!</p>

    <p>Ваш код подтверждения для <?= ConfirmCode::getPurposeLabel($purpose)?>:</p>

    <p><?= $code ?></p>
</div>
