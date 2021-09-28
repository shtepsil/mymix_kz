<?php
use yii\db\Expression;
use yii\db\Migration;

class m160804_073007_s_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%s_user}}', [
            'id' => $this->primaryKey(11),
            'username' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'password_reset_token' => $this->string(255)->null()->defaultExpression('null'),
            'email' => $this->string(255)->notNull(),
            'status' => $this->smallInteger(6)->notNull()->defaultValue(10),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
            'role' => $this->string(255)->null()->defaultExpression('null'),
        ], $tableOptions);
        $time = time();
        $this->insert('{{%s_user}}', [
            'id' => 1,
            'username' => 'admin@demo.kz',
            'auth_key' => '36rR8cB_q0IgMyozpb_cFGLy_4D_PkEd',
            'password_hash' => '$2y$13$Z.WVeGdyC6OzYN4AWFfTAuaIO5ZcEd1eM33Ne/X00cGQwIuHL7jDa',
            'password_reset_token' => new Expression('null'),
            'email' => 'admin@demo.kz',
            'status' => 10,
            'created_at' => $time,
            'updated_at' => $time,
            'role' => 'admin',
        ]);
        $this->createTable('{{%s_auth}}', [
            'id' => $this->primaryKey(11),
            'user_id' => $this->integer(11)->notNull(),
            'source' => $this->string(255)->notNull(),
            'source_id' => $this->string(255)->notNull(),
        ], $tableOptions);
        $this->createIndex('{{%idx-s_auth-user_id}}', '{{%s_auth}}', 'user_id');
        $this->addForeignKey('{{%fk-s_auth-user_id}}', '{{%s_auth}}', 'user_id', '{{%s_user}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function down()
    {
        $this->dropForeignKey('{{%fk-auth-user_id}}', '{{%s_auth}}');
        $this->dropTable('{{%s_auth}}');
        $this->dropTable('{{%s_user}}');
    }
}
