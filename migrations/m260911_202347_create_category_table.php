<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category}}`.
 */
class m260911_202347_create_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->null()->comment('ID родительской категории'),
            'name' => $this->string()->notNull()->comment('Название'),
            'slug' => $this->string()->notNull()->comment('ЧПУ'),
            'sort_order' => $this->smallInteger()->notNull()->comment('Порядок сортировки'),
            'is_active' => $this->boolean()->notNull()->defaultValue(1)->comment('Активен'),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->addForeignKey('fk-category-parent_id', '{{%category}}', 'parent_id', '{{%category}}', 'id', 'RESTRICT', 'CASCADE');
        $this->createIndex('idx-category-slug', '{{%category}}', 'slug', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%category}}');
    }
}
