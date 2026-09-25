<?php

use app\models\Category;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Category $model */

$this->title = 'Просмотр категории ' . $model->name;
?>
<div class="category-view">

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'parent_id',
                'format' => 'raw',
                'value' => function (Category $model) {
                    return $model->parent ? Html::a($model->parent->name, ['category/view', 'id' => $model->parent_id]) : null;
                }
            ],
            'name',
            'slug',
            'sort_order',
            'is_active',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
