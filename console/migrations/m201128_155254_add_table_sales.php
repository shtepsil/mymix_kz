<?php

use yii\db\Migration;

class m201128_155254_add_table_sales extends Migration
{
    public function up()
    {
        $this->createTable('sales', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'active' => $this->boolean(),
            'value' => $this->float(),
            'type_value' => $this->string(255),
            'goods' => 'JSON',
            'basket_sum_from' => $this->integer(11),
            'gifts' => 'JSON',
            'gifts_count' => $this->integer(2),
            'priority' => $this->integer(11)
        ]);
    }

    public function down()
    {
        $this->dropTable('sales');
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
