<?php
use yii\db\Migration;

/**
 * Handles the creation for table `l_source_message`.
 */
class m160804_075121_create_l_source_message_table extends Migration
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
        $this->createTable('{{%l_source_message}}', [
            'id' => $this->primaryKey(),
            'category' => $this->string(),
            'message' => $this->text(),
            'default' => $this->text(),
        ], $tableOptions);
        $this->createTable('{{%l_message}}', [
            'id' => $this->integer(11)->notNull(),
            'language' => $this->string(16)->notNull(),
            'translation' => $this->text()->null(),
        ], $tableOptions);
        $this->addPrimaryKey('{{%pk-l_message-id}}', '{{%l_message}}', ['id', 'language']);
        $this->addForeignKey('{{%fk-l_message-id}}', '{{%l_message}}', 'id', '{{%l_source_message}}', 'id', 'CASCADE', 'RESTRICT');
        $this->createIndex('{{%idx-l_source_message-category}}', '{{%l_source_message}}', 'category');
        $this->createIndex('{{%idx-l_message-language}}', '{{%l_message}}', 'language');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('{{%fk-l_message-id}}', '{{%l_message}}');
        $this->dropTable('{{%l_message}}');
        $this->dropTable('{{%l_source_message}}');
    }
}
