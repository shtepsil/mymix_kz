<?php

use yii\db\Migration;

/**
 * Handles the creation for table `module`.
 */
class m160804_081207_create_module_table extends Migration
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
        $this->createTable('{{%module}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'action' => $this->string(255)->notNull()->comment('Router'),
            'params' => $this->text()->comment('Дополнительные параметры'),
            'path' => $this->string(500)->comment('ЧПУ'),
        ],$tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%module}}');
    }
}
