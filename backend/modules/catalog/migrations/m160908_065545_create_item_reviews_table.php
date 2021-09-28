<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%item_reviews}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%items}}`
 */
class m160908_065545_create_item_reviews_table extends Migration
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
        $this->createTable('{{%item_reviews}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull()->comment('Пользователь'),
            'item_id' => $this->integer(11)->notNull()->comment('Товар'),
            'rate' => $this->boolean()->notNull()->comment('Оценка'),
            'name' => $this->string(255)->notNull()->comment('Имя'),
            'body' => $this->text()->notNull()->comment('Отзыв'),
            'isVisible' => $this->boolean()->notNull()->defaultValue(0)->comment('Видимость'),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ],$tableOptions);
        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-item_reviews-user_id}}',
            '{{%item_reviews}}',
            'user_id'
        );
        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-item_reviews-user_id}}',
            '{{%item_reviews}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-item_reviews-item_id}}',
            '{{%item_reviews}}',
            'item_id'
        );
        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-item_reviews-item_id}}',
            '{{%item_reviews}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-item_reviews-user_id}}',
            '{{%item_reviews}}'
        );
        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-item_reviews-user_id}}',
            '{{%item_reviews}}'
        );
        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-item_reviews-item_id}}',
            '{{%item_reviews}}'
        );
        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-item_reviews-item_id}}',
            '{{%item_reviews}}'
        );
        $this->dropTable('{{%item_reviews}}');
    }
}
