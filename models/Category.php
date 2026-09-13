<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property int|null $parent_id ID родительской категории
 * @property string $name Название
 * @property string $slug ЧПУ
 * @property int $sort_order Порядок сортировки
 * @property bool $is_active Активен
 * @property string|null $created_at Создан
 * @property string|null $updated_at Изменен
 *
 * @property Category[] $subCategories
 * @property Category $parent
 */
class Category extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%category}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['parent_id', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => true],
            [['name', 'slug'], 'filter', 'trim'],
            [['parent_id', 'sort_order', 'is_active'], 'integer'],
            [['is_active'], 'boolean'],
            [['name', 'slug', 'sort_order'], 'required'],
            [['created_at', 'updated_at'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'parent_id' => 'ID родительской категории',
            'name' => 'Название',
            'slug' => 'ЧПУ',
            'sort_order' => 'Порядок сортировки',
            'is_active' => 'Активен',
            'created_at' => 'Создан',
            'updated_at' => 'Изменен',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getSubCategories(): ActiveQuery
    {
        return $this->hasMany(Category::class, ['parent_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'parent_id']);
    }

}
