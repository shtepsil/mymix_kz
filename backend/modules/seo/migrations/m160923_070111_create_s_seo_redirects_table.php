<?php
namespace backend\modules\seo\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%s_seo_redirects}}`.
 */
class m160923_070111_create_s_seo_redirects_table extends Migration
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
        $this->createTable('{{%s_seo_redirects}}', [
            'id' => $this->primaryKey(),
            'old_url' => $this->string(255)->notNull()->comment('Старая ссылка'),
            'new_url' => $this->string(255)->notNull()->comment('Новая ссылка'),
            'type' => $this->string(3)->notNull()->defaultValue('301')->comment('Вид редиректа'),
            'isRegex' => $this->boolean()->notNull()->defaultValue(0)->comment('Регулярное выражение'),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ],$tableOptions);
        $this->createIndex('{{%idx-s_seo_redirects-old_url}}', '{{%s_seo_redirects}}', 'old_url', true);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('idx-s_seo_redirects-old_url', '{{%s_seo_redirects}}');
        $this->dropTable('{{%s_seo_redirects}}');
    }
}
