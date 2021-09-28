<?php

use yii\db\Migration;

class m200528_044826_add_delivery_field_order_table extends Migration
{
    public function up()
    {
        $this->addColumn('orders', 'delivery', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn('orders', 'delivery');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
