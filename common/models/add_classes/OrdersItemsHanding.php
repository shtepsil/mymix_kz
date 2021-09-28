<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_items_handing".
 *
 * @property integer $id
 * @property integer $orders_items_id
 * @property integer $type_handling_id
 *
 * @property OrdersItems $ordersItems
 * @property TypeHandling $typeHandling
 */
class OrdersItemsHanding extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_items_handing';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_items_id', 'type_handling_id'], 'required'],
            [['orders_items_id', 'type_handling_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orders_items_id' => 'Orders Items ID',
            'type_handling_id' => 'Type Handling ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersItems()
    {
        return $this->hasOne(OrdersItems::className(), ['id' => 'orders_items_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypeHandling()
    {
        return $this->hasOne(TypeHandling::className(), ['id' => 'type_handling_id']);
    }
}
