<?php

use yii\db\Migration;

class m200601_120157_add_field_our_stories_id_table_orders extends Migration
{
    public function up()
    {
        $this->addColumn('orders', 'our_stories_id', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn('orders', 'our_stories_id');
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
