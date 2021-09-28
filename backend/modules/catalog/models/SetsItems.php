<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "sets_items".
 *
 * @property integer $id
 * @property integer $set_id
 * @property integer $item_id
 * @property double $price
 * @property double $count
 *
 * @property Sets $set
 * @property Items $item
 */
class SetsItems extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sets_items';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['set_id', 'item_id'], 'integer'],
            [['set_id', 'item_id'], 'required'],
            [['price', 'count'], 'number'],
            ['set_id', 'exist', 'skipOnError' => true, 'targetClass' => Sets::className(), 'targetAttribute' => ['set_id' => 'id']],
            ['item_id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'set_id' => 'Набор',
            'item_id' => 'Товар',
            'price' => 'Цена',
            'count' => 'Количество в наборе'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSet()
    {
        return $this->hasOne(Sets::className(), ['id' => 'set_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'set_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Sets::className()
                ]
            ],
            'item_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Items::className()
                ]
            ],
            'price' => [],
            'count' => []
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