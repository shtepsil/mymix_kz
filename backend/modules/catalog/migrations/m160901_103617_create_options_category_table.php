<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%options_category}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%category}}`
 * - `{{%options}}`
 */
class m160901_103617_create_options_category_table extends Migration
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
        $this->createTable('{{%options_category}}', [
            'id' => $this->primaryKey(),
            'cid' => $this->integer(11)->notNull()->comment('Категория'),
            'option_id' => $this->integer(11)->notNull()->comment('Характеристика'),
            'isFilter' => $this->boolean()->notNull()->defaultValue(0)->comment('Использовать как фильтр'),
        ],$tableOptions);
        // creates index for column `cid`
        $this->createIndex(
            '{{%idx-options_category-cid}}',
            '{{%options_category}}',
            'cid'
        );
        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-options_category-cid}}',
            '{{%options_category}}',
            'cid',
            '{{%category}}',
            'id',
            'CASCADE'
        );
        // creates index for column `option_id`
        $this->createIndex(
            '{{%idx-options_category-option_id}}',
            '{{%options_category}}',
            'option_id'
        );
        // add foreign key for table `{{%options}}`
        $this->addForeignKey(
            '{{%fk-options_category-option_id}}',
            '{{%options_category}}',
            'option_id',
            '{{%options}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `{{%category}}`
        $this->dropForeignKey(
            '{{%fk-options_category-cid}}',
            '{{%options_category}}'
        );
        // drops index for column `cid`
        $this->dropIndex(
            '{{%idx-options_category-cid}}',
            '{{%options_category}}'
        );
        // drops foreign key for table `{{%options}}`
        $this->dropForeignKey(
            '{{%fk-options_category-option_id}}',
            '{{%options_category}}'
        );
        // drops index for column `option_id`
        $this->dropIndex(
            '{{%idx-options_category-option_id}}',
            '{{%options_category}}'
        );
        $this->dropTable('{{%options_category}}');
    }
}
