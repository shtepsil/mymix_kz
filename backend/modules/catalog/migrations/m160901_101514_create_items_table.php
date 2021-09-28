<?php
namespace backend\modules\catalog\migrations;

use Yii;
use yii\db\Migration;

/**
 * Handles the creation for table `{{%items}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%category}}`
 * - `{{%brands}}`
 */
class m160901_101514_create_items_table extends Migration
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
        $this->createTable('{{%items}}', [
            'id' => $this->primaryKey(),
            'cid' => $this->integer(11)->notNull()->comment('Категория'),
            'brand_id' => $this->integer(11)->null()->comment('Бренд'),
            'name' => $this->string(255)->notNull()->comment('Название'),
            'vendor_code' => $this->string(255)->null()->comment('Артикул'),
            'body_small' => $this->string(255)->null()->comment('Краткое Описание'),
            'body' => $this->text()->null()->comment('Описание'),
            'feature' => $this->text()->null()->comment('Характеристики'),
            'price' => $this->decimal(15, 4)->notNull()->comment('Цена'),
            'old_price' => $this->decimal(15, 4)->null()->comment('Старая цена'),
            'discount' => $this->decimal(15, 4)->null()->comment('Скидка'),
            'img_list' => $this->string(255)->null()->comment('Изображения для списковой'),
            'isHit' => $this->boolean()->notNull()->defaultValue(0)->comment('Хит'),
            'isNew' => $this->boolean()->notNull()->defaultValue(0)->comment('Новинка'),
            'isSale' => $this->boolean()->notNull()->defaultValue(0)->comment('Акция'),
            'isVisible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимость'),
            'popularity' => $this->integer(11)->null()->defaultValue(0)->comment('Популярность'),
            'rate' => $this->integer(11)->null()->defaultValue(0)->comment('Рейтинг'),
            'count_reviews' => $this->integer(11)->null()->defaultValue(0)->comment('Кол-во отзывов'),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ],$tableOptions);
        if (Yii::$app->function_system->enable_multi_lang()) {
            $this->createTable('{{%items_lang}}', [
                'id' => $this->primaryKey(),
                'owner_id' => $this->integer(11)->notNull(),
                'language' => $this->string(6)->notNull()->comment('Язык'),
                'name' => $this->string(255)->null()->comment('Название'),
                'body_small' => $this->string(255)->null()->comment('Краткое Описание'),
                'body' => $this->text()->null()->comment('Описание'),
            ],$tableOptions);
            $this->createIndex('{{%idx-items_lang-owner_id}}', '{{%items_lang}}', 'owner_id');
            $this->addForeignKey('{{%fk-items_lang-owner_id}}', '{{%items_lang}}', 'owner_id', '{{%items}}', 'id', 'CASCADE', 'RESTRICT');
            $this->createIndex('{{%idx-items_lang-language}}', '{{%items_lang}}', 'language');
        }
        // creates index for column `cid`
        $this->createIndex(
            '{{%idx-items-cid}}',
            '{{%items}}',
            'cid'
        );
        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-items-cid}}',
            '{{%items}}',
            'cid',
            '{{%category}}',
            'id',
            'CASCADE'
        );
        // creates index for column `brand_id`
        $this->createIndex(
            '{{%idx-items-brand_id}}',
            '{{%items}}',
            'brand_id'
        );
        // add foreign key for table `{{%brands}}`
        $this->addForeignKey(
            '{{%fk-items-brand_id}}',
            '{{%items}}',
            'brand_id',
            '{{%brands}}',
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
            $this->dropForeignKey('{{%fk-items_lang-owner_id}}', '{{%items_lang}}');
            $this->dropTable('{{%items_lang}}');
        }
        // drops foreign key for table `{{%category}}`
        $this->dropForeignKey(
            '{{%fk-items-cid}}',
            '{{%items}}'
        );
        // drops index for column `cid`
        $this->dropIndex(
            '{{%idx-items-cid}}',
            '{{%items}}'
        );
        // drops foreign key for table `{{%brands}}`
        $this->dropForeignKey(
            '{{%fk-items-brand_id}}',
            '{{%items}}'
        );
        // drops index for column `brand_id`
        $this->dropIndex(
            '{{%idx-items-brand_id}}',
            '{{%items}}'
        );
        $this->dropTable('{{%items}}');
    }
}
