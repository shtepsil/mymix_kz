<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{{%banners}}`.
 */
class m160905_042554_create_banners_table extends Migration
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
        $this->createTable('{{%banners}}', [
            'id' => $this->primaryKey(),
            'url' => $this->string(500)->notNull()->comment('Ссылка'),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'img' => $this->string(255)->notNull()->comment('Изображение'),
            'img_mob' => $this->string(255)->null()->comment('Изоб-ние моб.'),
            'img_table' => $this->string(255)->null()->comment('Изоб-ние планшет'),
            'isVisible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимость'),
            'sort' => $this->integer(11)->null()->defaultValue(0)->comment('Порядок'),
        ],$tableOptions);
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%banners_lang}}', [
                'id' => $this->primaryKey(),
                'owner_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'url' => $this->string(500)->null()->comment('Ссылка'),
                'name' => $this->string(255)->null()->comment('Название'),
            ],$tableOptions);
            $this->createIndex('{{%idx-banners_lang-owner_id}}', '{{%banners_lang}}', 'owner_id');
            $this->addForeignKey('{{%fk-banners_lang-owner_id}}', '{{%banners_lang}}', 'owner_id', '{{%banners}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-banners_lang-language}}', '{{%banners_lang}}', 'language');
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->dropForeignKey('{{%fk-banners_lang-owner_id}}', '{{%banners_lang}}');
            $this->dropTable('{{%banners_lang}}');
        }
        $this->dropTable('{{%banners}}');
    }
}
