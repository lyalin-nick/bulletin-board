<?php

use app\modules\admin\assets\AdminLteAsset;
use app\widgets\Alert;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $content */

AdminLteAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="noindex, nofollow">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: 'Вход') ?></title>
    <?= $this->render('_theme-init') ?>
    <?php $this->head() ?>
</head>
<body class="login-page bg-body-secondary">
<?php $this->beginBody() ?>

<main class="login-box">
    <?= Alert::widget() ?>
    <?= $content ?>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
