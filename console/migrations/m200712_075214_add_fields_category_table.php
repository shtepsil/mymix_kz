<?php

use yii\db\Migration;

class m200712_075214_add_fields_category_table extends Migration
{
    public function up()
    {
        $this->addColumn('category', 'img_menu', $this->string(255));
        $this->addColumn('category', 'img_banner_1', $this->string(255));
        $this->addColumn('category', 'link_banner_1', $this->string(255));
        $this->addColumn('category', 'img_banner_2', $this->string(255));
        $this->addColumn('category', 'link_banner_2', $this->string(255));
        $this->addColumn('category', 'img_banner_3', $this->string(255));
        $this->addColumn('category', 'link_banner_3', $this->string(255));
        $this->addColumn('category', 'img_banner_4', $this->string(255));
        $this->addColumn('category', 'link_banner_4', $this->string(255));
    }

    public function down()
    {
        $this->dropColumn('category', 'img_menu');
        $this->dropColumn('category', 'img_banner_1');
        $this->dropColumn('category', 'img_banner_2');
        $this->dropColumn('category', 'img_banner_3');
        $this->dropColumn('category', 'img_banner_4');
        $this->dropColumn('category', 'link_banner_1');
        $this->dropColumn('category', 'link_banner_2');
        $this->dropColumn('category', 'link_banner_3');
        $this->dropColumn('category', 'link_banner_4');
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
