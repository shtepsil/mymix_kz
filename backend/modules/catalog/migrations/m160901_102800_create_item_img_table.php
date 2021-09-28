<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%item_img}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%items}}`
 */
class m160901_102800_create_item_img_table extends Migration
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
        $this->createTable('{{%item_img}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer(11)->notNull()->comment('Товар'),
            'url' => $this->string(255)->notNull()->comment('Изоб-ние'),
        ],$tableOptions);
        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-item_img-item_id}}',
            '{{%item_img}}',
            'item_id'
        );
        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-item_img-item_id}}',
            '{{%item_img}}',
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
        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-item_img-item_id}}',
            '{{%item_img}}'
        );
        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-item_img-item_id}}',
            '{{%item_img}}'
        );
        $this->dropTable('{{%item_img}}');
    }
}
