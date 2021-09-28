<?php

use yii\db\Migration;

class m200601_120042_add_field_name_pickup_table_our_stories extends Migration
{
    public function up()
    {
        $this->addColumn('our_stores', 'name_pickup', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn('our_stores', 'name_pickup');
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
