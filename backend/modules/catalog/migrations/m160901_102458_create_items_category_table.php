<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%items_category}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%items}}`
 * - `{{%category}}`
 */
class m160901_102458_create_items_category_table extends Migration
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
        $this->createTable('{{%items_category}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer(11)->notNull()->comment('Товар'),
            'category_id' => $this->integer(11)->notNull()->comment('Категория'),
        ],$tableOptions);
        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-items_category-item_id}}',
            '{{%items_category}}',
            'item_id'
        );
        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-items_category-item_id}}',
            '{{%items_category}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );
        // creates index for column `category_id`
        $this->createIndex(
            '{{%idx-items_category-category_id}}',
            '{{%items_category}}',
            'category_id'
        );
        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-items_category-category_id}}',
            '{{%items_category}}',
            'category_id',
            '{{%category}}',
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
            '{{%fk-items_category-item_id}}',
            '{{%items_category}}'
        );
        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-items_category-item_id}}',
            '{{%items_category}}'
        );
        // drops foreign key for table `{{%category}}`
        $this->dropForeignKey(
            '{{%fk-items_category-category_id}}',
            '{{%items_category}}'
        );
        // drops index for column `category_id`
        $this->dropIndex(
            '{{%idx-items_category-category_id}}',
            '{{%items_category}}'
        );
        $this->dropTable('{{%items_category}}');
    }
}
