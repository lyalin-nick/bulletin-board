<?php

use app\models\Staff;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%staff}}`.
 */
class m260911_194253_create_staff_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%staff}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(Staff::STATUS_INACTIVE),
            'last_login_at' => $this->dateTime()->null()->comment('Последняя авторизация'),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->createIndex('idx-staff-status', '{{%staff}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%staff}}');
    }
}
