<?php
namespace backend\modules\catalog\migrations;

use Yii;
use yii\db\Migration;

/**
 * Handles the creation for table `{{%options_value}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%options}}`
 */
class m160901_103330_create_options_value_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%options_value}}', [
            'id' => $this->primaryKey(),
            'option_id' => $this->integer(11)->notNull()->comment('Характеристика'),
            'value' => $this->string(500)->notNull()->comment('Значение'),
        ],$tableOptions);
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%options_value_lang}}', [
                'id' => $this->primaryKey(),
                'owner_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'value' => $this->string(500)->notNull()->comment('Значение'),
            ],$tableOptions);
            $this->createIndex('{{%idx-options_value_lang-owner_id}}', '{{%options_value_lang}}', 'owner_id');
            $this->addForeignKey('{{%fk-options_value_lang-owner_id}}', '{{%options_value_lang}}', 'owner_id', '{{%options_value}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-options_value_lang-language}}', '{{%options_value_lang}}', 'language');
        }
        // creates index for column `option_id`
        $this->createIndex(
            '{{%idx-options_value-option_id}}',
            '{{%options_value}}',
            'option_id'
        );
        // add foreign key for table `{{%options}}`
        $this->addForeignKey(
            '{{%fk-options_value-option_id}}',
            '{{%options_value}}',
            'option_id',
            '{{%options}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->dropForeignKey('{{%fk-options_value_lang-owner_id}}', '{{%options_value_lang}}');
            $this->dropTable('{{%options_value_lang}}');
        }
        // drops foreign key for table `{{%options}}`
        $this->dropForeignKey(
            '{{%fk-options_value-option_id}}',
            '{{%options_value}}'
        );
        // drops index for column `option_id`
        $this->dropIndex(
            '{{%idx-options_value-option_id}}',
            '{{%options_value}}'
        );
        $this->dropTable('{{%options_value}}');
    }
}
