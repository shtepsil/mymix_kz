<?php
namespace backend\modules\catalog\migrations;

use yii\db\Migration;

class m161208_051440_modify_module extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE {{%item_options_value}}
MODIFY COLUMN `option_value_id`  int(11) NULL COMMENT \'Значение параметра из списка\' AFTER `option_id`');
        $this->addColumn('{{%item_options_value}}', 'max_value', $this->string(500)->null()->comment('Максимальное параметра характерискики'));
        $this->addColumn('{{%options}}', 'isFilter', $this->boolean()->notNull()->defaultValue(0)->comment('Использовать как фильтр'));
        $this->addColumn('{{%options}}', 'isList', $this->boolean()->notNull()->defaultValue(0)->comment('Использовать в списковой'));
        $this->addColumn('{{%options}}', 'isCompare', $this->boolean()->notNull()->defaultValue(0)->comment('Использовать в сравнение'));
        $this->addColumn('{{%options}}', 'measure', $this->string(50)->null()->comment('Единица измерения'));
        $this->addColumn('{{%options}}', 'measure_position', $this->string(50)->notNull()->defaultValue('right')->comment('Позиция ед. изм.'));
        $this->addColumn('{{%options_category}}', 'sort', $this->integer(11)->null()->defaultValue(0)->comment('Порядок'));
        $this->addColumn('{{%options_category}}', 'isList', $this->boolean()->notNull()->defaultValue(0)->comment('Использовать в списковой'));
        $this->addColumn('{{%options_category}}', 'isCompare', $this->boolean()->notNull()->defaultValue(0)->comment('Использовать в сравнение'));
    }
    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE {{%item_options_value}}
MODIFY COLUMN `option_value_id`  int(11) NOT NULL COMMENT \'Значение фильтра из списка\' AFTER `option_id`');
        $this->dropColumn('{{%item_options_value}}', 'max_value');
        $this->dropColumn('{{%options}}', 'isFilter');
        $this->dropColumn('{{%options}}', 'isList');
        $this->dropColumn('{{%options}}', 'isCompare');
        $this->dropColumn('{{%options}}', 'measure');
        $this->dropColumn('{{%options}}', 'measure_position');
        $this->dropColumn('{{%options_category}}', 'sort');
        $this->dropColumn('{{%options_category}}', 'isList');
        $this->dropColumn('{{%options_category}}', 'isCompare');
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
