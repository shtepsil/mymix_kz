<?php

use yii\db\Migration;

class m200516_043305_add_delivery_info_s_settings_table extends Migration
{
    public function up()
    {
        $this->insert('s_settings', [
            'group' => 'delivery_postexpress_tarifs',
            'key' => 'tarifs',
            'value' => null
        ]);

        $this->batchInsert('s_settings',
            ['group', 'key', 'value'], [
                ['group' => 'delivery', 'key' => 'delivery_method_dhl', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_dhl_text', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_kazpost_1', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_kazpost_1_text', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_kazpost_2', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_kazpost_2_text', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_postexpress_1', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_postexpress_1_text', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_postexpress_2', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_postexpress_2_text', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_pickup', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_pickup_text', 'value' => null],
                ['group' => 'address_pickup', 'key' => 'address_pickup_zip', 'value' => '050010'],
                ['group' => 'address_pickup', 'key' => 'address_pickup_city', 'value' => 'Алматы'],
                ['group' => 'delivery_kazpost', 'key' => 'delivery_kazpost_days', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_courier_1', 'value' => '1'],
                ['group' => 'delivery', 'key' => 'delivery_method_courier_1_text', 'value' => null],
                ['group' => 'delivery', 'key' => 'delivery_method_courier_2', 'value' => '1'],
                ['group' => 'delivery', 'key' => 'delivery_method_courier_2_text', 'value' => null],
                ['group' => 'delivery_postexpress_tarifs', 'key' => 'postexpress_tarifs', 'value' => '{"1":{"6":{"zone":"3","days":"5-8"},"11":{"zone":"3","days":"5-7"},"2":{"zone":"1","days":"2-4"},"4":{"zone":"3","days":"5-8"},"17":{"zone":"1","days":"3"},"12":{"zone":"5","days":"4-6"},"5":{"zone":"1","days":"2-4"},"18":{"zone":"2","days":"3-5"},"19":{"zone":"2","days":"4-6"},"9":{"zone":"2","days":"2-5"},"13":{"zone":"2","days":"4-6"},"8":{"zone":"2","days":"4-6"},"10":{"zone":"2","days":"5-7"},"14":{"zone":"1","days":"1-3"},"15":{"zone":"1","days":"1-3"},"20":{"zone":"3","days":"5-8"},"7":{"zone":"2","days":"6-8"},"3":{"zone":"1","days":"2-4"},"21":{"zone":"5","days":"7-10"}}}'],
                ['group' => 'delivery_postexpress_tarifs', 'key' => 'postexpress_tarifs_price', 'value' => '{"0.3":{"1":{"do":"1000","dd":"1400"},"2":{"do":"1150","dd":"1550"},"3":{"do":"1550","dd":"1700"},"5":{"do":"4650","dd":"4800"}},"1":{"1":{"do":"1000","dd":"1400"},"2":{"do":"1150","dd":"1550"},"3":{"do":"1550","dd":"1700"},"5":{"do":"4650","dd":"4800"}},"2":{"1":{"do":"1150","dd":"1550"},"2":{"do":"1300","dd":"1650"},"3":{"do":"1650","dd":"1850"},"5":{"do":"5300","dd":"5500"}},"5":{"1":{"do":"1300","dd":"1700"},"2":{"do":"1450","dd":"1800"},"3":{"do":"1850","dd":"2000"},"5":{"do":"6800","dd":"7000"}},"10":{"1":{"do":"1650","dd":"2000"},"2":{"do":"1750","dd":"2150"},"3":{"do":"2800","dd":"2900"},"5":{"do":"10150","dd":"10400"}},"15":{"1":{"do":"2450","dd":"2800"},"2":{"do":"2700","dd":"3000"},"3":{"do":"3350","dd":"3500"},"5":{"do":"11750","dd":"12000"}},"30":{"1":{"do":"3500","dd":"3700"},"2":{"do":"4200","dd":"4600"},"3":{"do":"5650","dd":"5900"},"5":{"do":"15200","dd":"16000"}},"50":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"75":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"100":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"150":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"300":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"500":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"750":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"1000":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"1500":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}},"2000":{"1":{"do":"","dd":""},"2":{"do":"","dd":""},"3":{"do":"","dd":""},"5":{"do":"","dd":""}}}']
            ]
        );
    }

    public function down()
    {
        $this->delete('s_settings', [
            'group' => 'delivery_postexpress_tarifs',
        ]);
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
