<?php

use yii\db\Migration;

class m200507_053634_add_field_zip_table_delivery_price extends Migration
{
    public function up()
    {
        $this->addColumn('delivery_price', 'zip', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn('delivery_price', 'zip');
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
