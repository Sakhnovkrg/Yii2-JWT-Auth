<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_refresh_token}}`.
 */
class m241009_074745_create_user_refresh_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_refresh_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string(32)->notNull()->unique(),
            'ip' => $this->string(50)->notNull(),
            'user_agent' => $this->string(1000)->notNull(),
            'created_at' => $this->dateTime()->notNull()
        ]);

        $this->createIndex('idx_user_refresh_token__token', '{{%user_refresh_token}}', 'token');
        $this->createIndex('idx_user_refresh_token__user_id', '{{%user_refresh_token}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_refresh_token}}');
    }
}
