<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

/**
 * Handles the creation for table `{{%orders}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m160902_081652_create_orders_table extends Migration
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
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->null()->comment('Пользователь'),
            'user_name' => $this->string(255)->notNull()->comment('ФИО'),
            'user_phone' => $this->string(255)->notNull()->comment('Телефон'),
            'user_mail' => $this->string(255)->null()->comment('E-Mail'),
            'user_city' => $this->string(255)->null()->comment('Город'),
            'user_address' => $this->string(500)->null()->comment('Адрес'),
            'user_comments' => $this->text()->null()->comment('Комментарий пользователя'),
            'isEntity' => $this->boolean()->notNull()->defaultValue(0)->comment('Юр.лицо'),
            'isFast' => $this->boolean()->notNull()->defaultValue(0)->comment('Быстрый заказ'),
            'full_price' => $this->decimal(15, 4)->notNull()->comment('Сумма заказа'),
            'price_delivery' => $this->decimal(15, 4)->null()->defaultValue(0)->comment('Стоимость доставки'),
            'delivery' => $this->string(100)->null()->comment('Способ доставки'),
            'payment' => $this->string(100)->null()->comment('Способ оплаты'),
            'status' => $this->string(100)->notNull()->defaultValue('0_new')->comment('Статус'),
            'admin_comments' => $this->text()->null()->comment('Ком-рий админ.'),
            'data' => $this->text()->null()->comment('Динамические поля'),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ],$tableOptions);
        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-orders-user_id}}',
            '{{%orders}}',
            'user_id'
        );
        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-orders-user_id}}',
            '{{%orders}}',
            'user_id',
            '{{%user}}',
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
            '{{%fk-orders-user_id}}',
            '{{%orders}}'
        );
        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-orders-user_id}}',
            '{{%orders}}'
        );
        $this->dropTable('{{%orders}}');
    }
}
