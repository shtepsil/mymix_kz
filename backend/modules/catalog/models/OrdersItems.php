<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "orders_items".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $item_id
 * @property double $count
 * @property double $price
 * @property string $data
 *
 * @property Items $item
 * @property Orders $order
 */
class OrdersItems extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_items';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'item_id'], 'integer'],
            [['order_id', 'item_id', 'count'], 'required'],
            [['count', 'price'], 'number'],
            [['data'], 'string'],
            ['item_id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            ['order_id', 'exist', 'skipOnError' => true, 'targetClass' => Orders::className(), 'targetAttribute' => ['order_id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Заказ',
            'item_id' => 'Товар',
            'count' => 'Кол-во',
            'price' => 'Цена',
            'data' => 'Данные модели на момент заказа'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::className(), ['id' => 'order_id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'order_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Orders::className()
                ]
            ],
            'item_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Items::className()
                ]
            ],
            'count' => [],
            'price' => [],
            'data' => []
        ];
        $result = [
            'form_action' => ["{$controller_name}/save"],
            'cancel' => ["{$controller_name}/index"],
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