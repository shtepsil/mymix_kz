<?php
namespace backend\modules\seo\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%seo}}`.
 */
class m160804_110848_create_seo_table extends Migration
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
        $this->createTable('{{%seo}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(50)->notNull(),
            'owner_id' => $this->integer(11)->notNull(),
            'description' => $this->string(500)->null(),
            'keywords' => $this->string(500)->null(),
            'title' => $this->string(500)->null(),
        ],$tableOptions);
        $this->createTable('{{%seo_lang}}', [
            'id' => $this->primaryKey(),
            'owner_id' => $this->integer(11)->notNull(),
            'lang_id' => $this->string(6)->notNull()->comment('Язык'),
            'description' => $this->string(500)->null(),
            'keywords' => $this->string(500)->null(),
            'title' => $this->string(500)->null(),
        ],$tableOptions);
        $this->createIndex('{{%idx-seo_lang-settings_id}}', '{{%seo_lang}}', 'owner_id');
        $this->addForeignKey('{{%fk-seo_lang-settings_id}}', '{{%seo_lang}}', 'owner_id', '{{%seo}}', 'id', 'CASCADE', 'RESTRICT');
        $this->createIndex('{{%idx-seo_lang-lang_id}}', '{{%seo_lang}}', 'lang_id');
        $this->insert('{{%seo}}', [
            'id' => 1,
            'type' => 'main',
            'owner_id' => 1,
            'description' => '',
            'keywords' => '',
            'title' => '',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('{{%fk-seo_lang-settings_id}}', '{{%seo_lang}}');
        $this->dropTable('{{%seo_lang}}');
        $this->dropTable('{{%seo}}');
    }
}
