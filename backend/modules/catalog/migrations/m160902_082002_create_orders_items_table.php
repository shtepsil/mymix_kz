<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%orders_items}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%orders}}`
 * - `{{%items}}`
 */
class m160902_082002_create_orders_items_table extends Migration
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
        $this->createTable('{{%orders_items}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(11)->notNull()->comment('Заказ'),
            'item_id' => $this->integer(11)->notNull()->comment('Товар'),
            'count' => $this->decimal(15, 4)->notNull()->comment('Кол-во'),
            'price' => $this->decimal(15, 4)->notNull()->defaultValue(0)->comment('Цена'),
            'data' => $this->text()->null()->comment('Данные модели на момент заказа'),
        ],$tableOptions);
        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-orders_items-order_id}}',
            '{{%orders_items}}',
            'order_id'
        );
        // add foreign key for table `{{%orders}}`
        $this->addForeignKey(
            '{{%fk-orders_items-order_id}}',
            '{{%orders_items}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE'
        );
        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-orders_items-item_id}}',
            '{{%orders_items}}',
            'item_id'
        );
        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-orders_items-item_id}}',
            '{{%orders_items}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `{{%orders}}`
        $this->dropForeignKey(
            '{{%fk-orders_items-order_id}}',
            '{{%orders_items}}'
        );
        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-orders_items-order_id}}',
            '{{%orders_items}}'
        );
        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-orders_items-item_id}}',
            '{{%orders_items}}'
        );
        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-orders_items-item_id}}',
            '{{%orders_items}}'
        );
        $this->dropTable('{{%orders_items}}');
    }
}
