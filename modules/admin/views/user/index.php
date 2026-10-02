<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\modules\admin\searches\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'surname',
            [
                'attribute' => 'email',
                'value' => fn(User $model) => $model->email . ($model->email_verified_at ? ' (' . date('d-m-Y H:i:s', strtotime($model->email_verified_at)) . ')' : '')
            ],
            [
                'attribute' => 'phone',
                'value' => function (User $model) {
                    if (!$model->phone) {
                        return null;
                    }
                    return $model->phone . ($model->phone_verified_at ? ' (' . date('d-m-Y H:i:s', strtotime($model->phone_verified_at)) . ')' : '');
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'html',
                'value' => fn(User $model) => $model->statusLabel,
            ],
            'created_at:datetime',
            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, User $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

</div>
