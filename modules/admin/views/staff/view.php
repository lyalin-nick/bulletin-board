<?php

use app\models\Staff;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Staff $model */

$this->title = $model->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="staff-view">

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= $model->isActive() ? Html::a('Деактивировать', ['deactivate', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите деактивировать сотрудника?',
                'method' => 'post',
            ],
        ]) : Html::a('Активировать', ['activate', 'id' => $model->id], [
            'class' => 'btn btn-outline-success',
            'data' => [
                'confirm' => 'Вы уверены, что хотите активировать сотрудника?',
                'method' => 'post',
            ],
        ])?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'full_name',
            'email:email',
            [
                'attribute' => 'status',
                'value' => fn(Staff $model) => $model->statusLabel,
            ],
            [
                'label' => 'Роль',
                'value' => fn(Staff $model) => $model->roleLabel,
            ],
            'last_login_at:datetime',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
