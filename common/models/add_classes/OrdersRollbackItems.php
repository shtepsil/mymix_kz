<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_rollback_items".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $item_order_id
 * @property double $count
 * @property integer $type
 * @property double $weight
 *
 * @property Orders $order
 * @property OrdersItems $itemOrder
 */
class OrdersRollbackItems extends \shadow\SActiveRecord
{
    public $type;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_rollback_items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'item_order_id', 'count'], 'required'],
            [['order_id', 'item_order_id', 'type'], 'integer'],
            [['count', 'weight'], 'number']
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
            'item_order_id' => 'Товар',
            'count' => 'Количество',
            'type' => 'Вид возврата',
            'weight' => 'Вес',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemOrder()
    {
        return $this->hasOne(OrdersItems::className(), ['id' => 'item_order_id']);
    }
    public $data_types = [
        0 => 'Полный',
        1 => 'Частичный'
    ];
    public $data_types_item = [
        0 => 'Поставщику',
        1 => 'Убытки'
    ];
}
