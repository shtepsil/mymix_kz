<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%s_settings}}`.
 */
class m160804_110028_create_s_settings_table extends Migration
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
        $this->createTable('{{%s_settings}}', [
            'id' => $this->primaryKey(),
            'group' => $this->string(50)->notNull()->comment('Группа'),
            'key' => $this->string(50)->notNull()->comment('Ключ'),
            'value' => $this->text()->null()->comment('Значение'),
        ],$tableOptions);
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%s_settings_lang}}', [
                'id' => $this->primaryKey(),
                'settings_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'value' => $this->text()->null()->comment('Значение'),
            ],$tableOptions);
            $this->createIndex('{{%idx-s_settings_lang-settings_id}}', '{{%s_settings_lang}}', 'settings_id');
            $this->addForeignKey('{{%fk-s_settings_lang-settings_id}}', '{{%s_settings_lang}}', 'settings_id', '{{%s_settings}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-s_settings_lang-language}}', '{{%s_settings_lang}}', 'language');
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->dropForeignKey('{{%fk-s_settings_lang-settings_id}}', '{{%s_settings_lang}}');
            $this->dropTable('{{%s_settings_lang}}');
        }
        $this->dropTable('{{%s_settings}}');
    }
}
