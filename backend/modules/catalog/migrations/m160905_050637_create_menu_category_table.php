<?php
namespace backend\modules\catalog\migrations;

use Yii;
use yii\db\Migration;

/**
 * Handles the creation for table `{{%menu_category}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%menu_category}}`
 */
class m160905_050637_create_menu_category_table extends Migration
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
        $this->createTable('{{%menu_category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'type' => $this->string(50)->notNull()->comment('Тип'),
            'owner_id' => $this->integer(11)->null(),
            'url' => $this->string(500)->null()->comment('Ссылка'),
            'sort' => $this->integer(11)->notNull()->defaultValue(0)->comment('Порядок'),
            'parent_id' => $this->integer(11)->null()->comment('Родитель'),
            'isVisible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимость'),
        ]);
        // creates index for column `parent_id`
        $this->createIndex(
            '{{%idx-menu_category-parent_id}}',
            '{{%menu_category}}',
            'parent_id'
        );
        // add foreign key for table `{{%menu_category}}`
        $this->addForeignKey(
            '{{%fk-menu_category-parent_id}}',
            '{{%menu_category}}',
            'parent_id',
            '{{%menu_category}}',
            'id',
            'CASCADE'
        );
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%menu_category_lang}}', [
                'id' => $this->primaryKey(),
                'menu_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'name' => $this->string(255)->null()->comment('Название'),
            ]);
            $this->createIndex('{{%idx-menu_category_lang-menu_id}}', '{{%menu_category_lang}}', 'menu_id');
            $this->addForeignKey('{{%fk-menu_category_lang-menu_id}}', '{{%menu_category_lang}}', 'menu_id', '{{%menu_category}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-menu_category_lang-language}}', '{{%menu_category_lang}}', 'language');
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->dropForeignKey('{{%fk-menu_category_lang-menu_id}}', '{{%menu_category_lang}}');
            $this->dropTable('{{%menu_category_lang}}');
        }
        // drops foreign key for table `{{%menu_category}}`
        $this->dropForeignKey(
            '{{%fk-menu_category-parent_id}}',
            '{{%menu_category}}'
        );
        // drops index for column `parent_id`
        $this->dropIndex(
            '{{%idx-menu_category-parent_id}}',
            '{{%menu_category}}'
        );
        $this->dropTable('{{%menu_category}}');
    }
}
