<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_access_token}}`.
 */
class m260826_070147_create_user_access_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%user_access_token}}', [
            'id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->notNull()->comment('ID пользователя'),
            'token' => $this->string(255)->notNull()->unique()->comment('Хэш токена'),
            'token_expired_at' => $this->dateTime()->notNull()->comment('Время истечения токена'),
            'refresh_token' => $this->string(255)->notNull()->unique()->comment('Хеш токена обновления'),
            'refresh_token_expired_at' => $this->dateTime()->notNull()->comment('Время истечения токена обновления'),
            'device_name' => $this->string(255)->null()->comment('Название устройства'),
            'user_agent' => $this->text()->null()->comment('Заголовок User-Agent'),
            'ip' => $this->string(45)->null()->comment('IP'),
            'last_used_at' => $this->dateTime()->null()->comment('Время последнего использования'),
            'revoked_at' => $this->dateTime()->null()->comment('Время отзыва токена'),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->addForeignKey('fk-user_access_token-user_id', '{{%user_access_token}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('fk-user_access_token-user_id', '{{%user_access_token}}');
        $this->dropTable('{{%user_access_token}}');
    }
}
