<?php
namespace backend\modules\seo\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%s_seo_urls}}`.
 */
class m160923_070105_create_s_seo_urls_table extends Migration
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
        $this->createTable('{{%s_seo_urls}}', [
            'id' => $this->primaryKey(),
            'resource' => $this->string(500)->notNull()->comment('Namespace модели'),
            'resource_id' => $this->integer(11)->notNull()->comment('Pk модели'),
            'controller' => $this->string(255)->notNull()->comment('Контроллер'),
            'action' => $this->string(255)->notNull()->comment('Action'),
            'path' => $this->string(255)->notNull()->comment('Полный ЧПУ'),
            'url' => $this->string(255)->notNull()->comment('ЧПУ'),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ],$tableOptions);
        $this->createIndex('{{%idx-s_seo_urls-path}}', '{{%s_seo_urls}}', 'path', true);
        $this->createIndex('{{%idx-s_seo_urls-resource_id}}', '{{%s_seo_urls}}', 'resource_id');
        $this->createIndex('{{%idx-s_seo_urls-controller}}', '{{%s_seo_urls}}', 'controller');
        $this->createIndex('{{%idx-s_seo_urls-action}}', '{{%s_seo_urls}}', 'action');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('idx-s_seo_urls-path', '{{%s_seo_urls}}');
        $this->dropIndex('idx-s_seo_urls-resource_id', '{{%s_seo_urls}}');
        $this->dropIndex('idx-s_seo_urls-controller', '{{%s_seo_urls}}');
        $this->dropIndex('idx-s_seo_urls-action', '{{%s_seo_urls}}');
        $this->dropTable('{{%s_seo_urls}}');
    }
}
