<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "item_accessory".
 *
 * @property integer $id
 * @property integer $item_id_main
 * @property integer $item_id_accessory
 *
 * @property Items $itemIdMain
 * @property Items $itemIdAccessory
 */
class ItemAccessory extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item_accessory';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id_main', 'item_id_accessory'], 'integer'],
            [['item_id_main', 'item_id_accessory'], 'required'],
            ['item_id_main', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id_main' => 'id']],
            ['item_id_accessory', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id_accessory' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_id_main' => 'Item Id Main',
            'item_id_accessory' => 'Item Id Accessory'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemIdMain()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id_main']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemIdAccessory()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id_accessory']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'item_id_main' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Items::className()
                ]
            ],
            'item_id_accessory' => [
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