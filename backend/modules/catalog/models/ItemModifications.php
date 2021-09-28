<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "item_modifications".
 *
 * @property integer $id
 * @property integer $item_main_id
 * @property integer $item_mod_id
 *
 * @property Items $itemMain
 * @property Items $itemMod
 */
class ItemModifications extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item_modifications';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_main_id', 'item_mod_id'], 'integer'],
            [['item_main_id', 'item_mod_id'], 'required'],
            ['item_main_id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_main_id' => 'id']],
            ['item_mod_id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_mod_id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_main_id' => 'Основной товар',
            'item_mod_id' => 'Модификация товара'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMain()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_main_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMod()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_mod_id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'item_main_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Items::className()
                ]
            ],
            'item_mod_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Items::className()
                ]
            ]
        ];
        $result = [
            'form_action' => [$controller_name . '/save'],
            'cancel' => [$controller_name . '/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields
                ]
            ]
        ];
        return $result;
    }
}