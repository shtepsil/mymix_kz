<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%item_options_value}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%items}}`
 * - `{{%options}}`
 * - `{{%options_value}}`
 */
class m160901_105135_create_item_options_value_table extends Migration
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
        $this->createTable('{{%item_options_value}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer(11)->notNull()->comment('Товар'),
            'option_id' => $this->integer(11)->notNull()->comment('Характеристика'),
            'option_value_id' => $this->integer(11)->notNull()->comment('Значение параметра из списка'),
            'value' => $this->string(500)->null()->comment('Своё параметра фильтра'),
        ],$tableOptions);
        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-item_options_value-item_id}}',
            '{{%item_options_value}}',
            'item_id'
        );
        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-item_options_value-item_id}}',
            '{{%item_options_value}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );
        // creates index for column `option_id`
        $this->createIndex(
            '{{%idx-item_options_value-option_id}}',
            '{{%item_options_value}}',
            'option_id'
        );
        // add foreign key for table `{{%options}}`
        $this->addForeignKey(
            '{{%fk-item_options_value-option_id}}',
            '{{%item_options_value}}',
            'option_id',
            '{{%options}}',
            'id',
            'CASCADE'
        );
        // creates index for column `option_value_id`
        $this->createIndex(
            '{{%idx-item_options_value-option_value_id}}',
            '{{%item_options_value}}',
            'option_value_id'
        );
        // add foreign key for table `{{%options_value}}`
        $this->addForeignKey(
            '{{%fk-item_options_value-option_value_id}}',
            '{{%item_options_value}}',
            'option_value_id',
            '{{%options_value}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-item_options_value-item_id}}',
            '{{%item_options_value}}'
        );
        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-item_options_value-item_id}}',
            '{{%item_options_value}}'
        );
        // drops foreign key for table `{{%options}}`
        $this->dropForeignKey(
            '{{%fk-item_options_value-option_id}}',
            '{{%item_options_value}}'
        );
        // drops index for column `option_id`
        $this->dropIndex(
            '{{%idx-item_options_value-option_id}}',
            '{{%item_options_value}}'
        );
        // drops foreign key for table `{{%options_value}}`
        $this->dropForeignKey(
            '{{%fk-item_options_value-option_value_id}}',
            '{{%item_options_value}}'
        );
        // drops index for column `option_value_id`
        $this->dropIndex(
            '{{%idx-item_options_value-option_value_id}}',
            '{{%item_options_value}}'
        );
        $this->dropTable('{{%item_options_value}}');
    }
}
