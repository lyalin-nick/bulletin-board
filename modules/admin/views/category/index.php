<?php

use app\models\Category;
use himiklab\sortablegrid\SortableGridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;

/** @var yii\web\View $this */
/** @var app\modules\admin\searches\CategorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Категории';
?>
<div class="category-index">
    <p>
        <?= Html::a('Добавить', ['create', 'parent_id' => $searchModel->parent_id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Структура', ['tree'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function (Category $model) {
                    return Html::a($model->name, ['index', 'CategorySearch' => ['parent_id' => $model->id]]);
                }
            ],
            'slug',
            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Category $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

</div>
