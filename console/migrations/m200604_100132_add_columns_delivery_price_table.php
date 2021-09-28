<?php

use yii\db\Migration;

class m200604_100132_add_columns_delivery_price_table extends Migration
{
    public function up()
    {
        $this->addColumn('delivery_price', 'delivery_method_courier_1_price', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_1_free_sum', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_1_min_sum', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_1_days', $this->string(250));
        $this->addColumn('delivery_price', 'delivery_method_courier_1_text', $this->text());
        $this->addColumn('delivery_price', 'delivery_method_courier_2_price', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_2_free_sum', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_2_min_sum', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_2_days', $this->string(250));
        $this->addColumn('delivery_price', 'delivery_method_courier_2_text', $this->text());
        $this->addColumn('delivery_price', 'delivery_method_courier_3_price', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_3_free_sum', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_3_max_sum', $this->float());
        $this->addColumn('delivery_price', 'delivery_method_courier_3_days', $this->string(250));
        $this->addColumn('delivery_price', 'delivery_method_courier_3_text', $this->text());
        $this->addColumn('delivery_price', 'delivery_method_pickup_text', $this->text());
    }

    public function down()
    {
        $this->dropColumn('delivery_price', 'delivery_method_courier_1_price');
        $this->dropColumn('delivery_price', 'delivery_method_courier_1_free_sum');
        $this->dropColumn('delivery_price', 'delivery_method_courier_1_min_sum');
        $this->dropColumn('delivery_price', 'delivery_method_courier_1_days');
        $this->dropColumn('delivery_price', 'delivery_method_courier_1_text');
        $this->dropColumn('delivery_price', 'delivery_method_courier_2_price');
        $this->dropColumn('delivery_price', 'delivery_method_courier_2_free_sum');
        $this->dropColumn('delivery_price', 'delivery_method_courier_2_min_sum');
        $this->dropColumn('delivery_price', 'delivery_method_courier_2_days');
        $this->dropColumn('delivery_price', 'delivery_method_courier_2_text');
        $this->dropColumn('delivery_price', 'delivery_method_courier_3_price');
        $this->dropColumn('delivery_price', 'delivery_method_courier_3_free_sum');
        $this->dropColumn('delivery_price', 'delivery_method_courier_3_max_sum');
        $this->dropColumn('delivery_price', 'delivery_method_courier_3_days');
        $this->dropColumn('delivery_price', 'delivery_method_courier_3_text');
        $this->dropColumn('delivery_price', 'delivery_method_pickup_text');
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
