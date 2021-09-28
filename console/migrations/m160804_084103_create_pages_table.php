<?php
use yii\db\Migration;

/**
 * Handles the creation for table `pages`.
 */
class m160804_084103_create_pages_table extends Migration
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
        $this->createTable('{{%pages}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'body' => $this->text()->notNull()->comment('Текст'),
            'isVisible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимость'),
            'not_delete' => $this->boolean()->defaultValue(0),
        ],$tableOptions);
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%pages_lang}}', [
                'id' => $this->primaryKey(),
                'page_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'name' => $this->string(255)->null()->comment('Название'),
                'body' => $this->text()->null()->comment('Текст'),
            ],$tableOptions);
            $this->createIndex('{{%idx-pages_lang-page_id}}', '{{%pages_lang}}', 'page_id');
            $this->addForeignKey('{{%fk-pages_lang-page_id}}', '{{%pages_lang}}', 'page_id', '{{%pages}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-pages_lang-language}}', '{{%pages_lang}}', 'language');
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->dropForeignKey('{{%fk-pages_lang-page_id}}', '{{%pages_lang}}');
            $this->dropTable('{{%pages_lang}}');
        }
        $this->dropTable('{{%pages}}');
    }
}
