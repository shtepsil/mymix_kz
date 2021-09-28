<?php
use yii\db\Migration;

/**
 * Handles the creation for table `{{%menu}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%menu}}`
 */
class m160804_105245_create_menu_table extends Migration
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
        $this->createTable('{{%menu}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'type' => $this->string(50)->notNull()->comment('Тип'),
            'owner_id' => $this->integer(11)->null(),
            'url' => $this->string(500)->null()->comment('Ссылка'),
            'sort' => $this->integer(11)->notNull()->defaultValue(0)->comment('Порядок'),
            'parent_id' => $this->integer(11)->null()->comment('Родитель'),
            'isVisible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимость'),
        ],$tableOptions);
        // creates index for column `parent_id`
        $this->createIndex(
            '{{%idx-menu-parent_id}}',
            '{{%menu}}',
            'parent_id'
        );
        // add foreign key for table `{{%menu}}`
        $this->addForeignKey(
            '{{%fk-menu-parent_id}}',
            '{{%menu}}',
            'parent_id',
            '{{%menu}}',
            'id',
            'CASCADE'
        );
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%menu_lang}}', [
                'id' => $this->primaryKey(),
                'menu_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'name' => $this->string(255)->null()->comment('Название'),
            ],$tableOptions);
            $this->createIndex('{{%idx-menu_lang-menu_id}}', '{{%menu_lang}}', 'menu_id');
            $this->addForeignKey('{{%fk-menu_lang-menu_id}}', '{{%menu_lang}}', 'menu_id', '{{%menu}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-menu_lang-language}}', '{{%menu_lang}}', 'language');
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->dropForeignKey('{{%fk-menu_lang-menu_id}}', '{{%menu_lang}}');
            $this->dropTable('{{%menu_lang}}');
        }
        // drops foreign key for table `{{%menu}}`
        $this->dropForeignKey(
            '{{%fk-menu-parent_id}}',
            '{{%menu}}'
        );
        // drops index for column `parent_id`
        $this->dropIndex(
            '{{%idx-menu-parent_id}}',
            '{{%menu}}'
        );
        $this->dropTable('{{%menu}}');
    }
}
