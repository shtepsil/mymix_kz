<?php

use yii\db\Migration;

class m200504_142436_add_field_delivery_methods_delivery_price extends Migration
{
    public function up()
    {
        $this->addColumn('delivery_price', 'delivery_methods', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn('delivery_price', 'delivery_methods');
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
