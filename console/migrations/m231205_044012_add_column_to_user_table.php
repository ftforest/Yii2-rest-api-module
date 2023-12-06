<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m231205_044012_add_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'surname', $this->string());
        $this->addColumn('user', 'phone', $this->string());
        $this->addColumn('user', 'access_token', $this->string());
        $this->addColumn('user', 'type', $this->string());
        // Добавьте остальные необходимые колонки
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'surname');
        $this->dropColumn('user', 'phone');
        $this->dropColumn('user', 'access_token');
        $this->dropColumn('user', 'type');
        // Добавьте остальные необходимые действия для отката миграции
    }
}
