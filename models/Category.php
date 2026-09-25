<?php

namespace app\models;

use himiklab\sortablegrid\SortableGridBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

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

    public static function getParents(int|null $id = null): array
    {
        $categoryQuery = Category::find();
        if ($id !== null) {
            $categoryQuery->where(['!=', 'id', $id]);
        }
        return ArrayHelper::map($categoryQuery->all(), 'id', 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['parent_id', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['is_active'], 'default', 'value' => true],
            [['name', 'slug'], 'filter', 'filter' => 'trim'],
            [['parent_id', 'sort_order'], 'integer'],
            [['is_active'], 'boolean'],
            [['name', 'slug'], 'required'],
            [['created_at', 'updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['parent_id' => 'id']],
            ['parent_id', 'validateParent'],
        ];
    }

    public function validateParent(string $attribute, array|null $params): void
    {
        if ($this->parent_id === null) {
            return;
        }

        if ((int) $this->parent_id === (int) $this->id) {
            $this->addError($attribute, 'Категория не может быть родителем самой себя.');

            return;
        }

        $node = static::findOne((int) $this->parent_id);
        $seen = [];

        while ($node !== null) {
            if (isset($seen[$node->id])) {
                break;
            }

            if ((int) $node->id === (int) $this->id) {
                $this->addError($attribute, 'Нельзя переместить категорию внутрь её собственной подкатегории.');

                return;
            }

            $seen[$node->id] = true;
            $node = $node->parent;
        }
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
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'immutable' => true,
                'ensureUnique' => true,
            ],
            'sort' => [
                'class' => SortableGridBehavior::class,
                'sortableAttribute' => 'sort_order'
            ],
        ];
    }

    public function beforeValidate(): bool
    {
        if (is_string($this->slug)) {
            $this->slug = trim($this->slug);
        }

        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'parent_id' => 'Родительская категория',
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

    public function getPath(): array
    {
        $path = [];
        $node = $this;
        $seen = [];

        while ($node !== null) {
            if (isset($seen[$node->id])) {
                break;
            }

            $seen[$node->id] = true;
            array_unshift($path, $node);
            $node = $node->parent;
        }

        return $path;
    }
}
