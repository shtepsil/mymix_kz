<?php
namespace backend\modules\catalog\migrations;

use Yii;
use yii\db\Migration;

/**
 * Handles the creation for table `{{%category}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%category}}`
 */
class m160901_092529_create_category_table extends Migration
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
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'isVisible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимость'),
            'parent_id' => $this->integer(11)->null()->comment('Родитель'),
            'sort' => $this->integer(11)->notNull()->defaultValue(0)->comment('Порядок'),
            'type' => $this->string(20)->notNull()->comment('Тип'),
        ],$tableOptions);
        // creates index for column `parent_id`
        $this->createIndex(
            '{{%idx-category-parent_id}}',
            '{{%category}}',
            'parent_id'
        );
        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-category-parent_id}}',
            '{{%category}}',
            'parent_id',
            '{{%category}}',
            'id',
            'CASCADE'
        );
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%category_lang}}', [
                'id' => $this->primaryKey(),
                'owner_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'name' => $this->string(255)->null()->comment('Название'),
            ],$tableOptions);
            $this->createIndex('{{%idx-category_lang-owner_id}}', '{{%category_lang}}', 'owner_id');
            $this->addForeignKey('{{%fk-category_lang-owner_id}}', '{{%category_lang}}', 'owner_id', '{{%category}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-category_lang-language}}', '{{%category_lang}}', 'language');
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->dropForeignKey('{{%fk-category_lang-owner_id}}', '{{%category_lang}}');
            $this->dropTable('{{%category_lang}}');
        }
        // drops foreign key for table `{{%category}}`
        $this->dropForeignKey(
            '{{%fk-category-parent_id}}',
            '{{%category}}'
        );
        // drops index for column `parent_id`
        $this->dropIndex(
            '{{%idx-category-parent_id}}',
            '{{%category}}'
        );
        $this->dropTable('{{%category}}');
    }
}
