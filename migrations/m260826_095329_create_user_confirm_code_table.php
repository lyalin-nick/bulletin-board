<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_confirm_code}}`.
 */
class m260826_095329_create_user_confirm_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%user_confirm_code}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull()->comment('ID пользователя'),
            'channel' => $this->string(8)->notNull()->comment('Канал отправки'),
            'purpose' => $this->string(16)->notNull(),
            'target' => $this->string(255)->notNull(),
            'code_hash' => $this->string(255)->notNull(),
            'expires_at' => $this->dateTime()->notNull(),
            'attempts' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'confirmed_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ]);
        $this->addForeignKey('fk-user_confirm_code-user_id', '{{%user_confirm_code}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx-user_confirm_code-search', '{{%user_confirm_code}}', ['user_id', 'purpose', 'channel']);
        $this->createIndex('idx-user_confirm_code-expire', '{{%user_confirm_code}}', ['expires_at']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%user_confirm_code}}');
    }
}
