<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%user}}`.
 */
class m160804_111259_create_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        ///Так должна выглядить таблица на всех проектах дополнительные поля создавать в таблице user_details
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'surname' => $this->string(255)->null()->comment('Фамилия'),
            'username' => $this->string(255)->notNull()->comment('Имя'),
            'patronymic' => $this->string(255)->null()->comment('Отчество'),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'password_reset_token' => $this->string(255)->null()->defaultExpression('null'),
            'email' => $this->string(255)->notNull()->comment('Почта'),
            'phone' => $this->string(255)->null()->comment('Телефон'),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(10),
            'data' => $this->text()->null()->comment('Доп. поля'),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ],$tableOptions);
        $this->createTable('{{%auth}}', [
            'id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->notNull(),
            'source' => $this->string(255)->notNull(),
            'source_id' => $this->string(255)->notNull(),
        ],$tableOptions);
        $this->createIndex('{{%idx-auth-user_id}}', '{{%auth}}', 'user_id');
        $this->addForeignKey('{{%fk-auth-user_id}}', '{{%auth}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'RESTRICT');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('{{%fk-auth-user_id}}', '{{%auth}}');
        $this->dropTable('{{%auth}}');
        $this->dropTable('{{%user}}');
    }
}
