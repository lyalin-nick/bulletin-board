<?php

use app\models\Staff;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\admin\searches\StaffSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Сотрудники';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="staff-index">

    <p>
        <?= Html::a('Добавить сотрудника', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'label' => 'Роль',
                'value' => fn(Staff $model) => $model->roleLabel,
            ],
            'full_name',
            'email:email',
            [
                'attribute' => 'status',
                'filter' => Html::activeDropDownList($searchModel, 'status', Staff::$statusLabels, ['class' => 'form-control', 'prompt' => 'Все']),
                'format' => 'raw',
                'value' => fn(Staff $model) => $model->statusLabel,
            ],
            'last_login_at',
            'created_at',
            'updated_at',
            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Staff $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>


</div>
