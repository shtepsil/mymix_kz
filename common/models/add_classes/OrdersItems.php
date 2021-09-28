<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_items".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $item_id
 * @property string $count
 * @property integer $price
 * @property double $weight
 * @property integer $purch_price
 * @property double $bonus_manager
 * @property string $data
 *
 * @property Orders $order
 * @property Items $item
 * @property OrdersItemsHanding[] $ordersItemsHandings
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
            [['order_id', 'item_id', 'count', 'price', 'purch_price'], 'required'],
            [['order_id', 'item_id', 'price', 'purch_price'], 'integer'],
            [['count','bonus_manager'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'item_id' => 'Item ID',
            'count' => 'Count',
            'purch_price' => 'PurchPrice',
            'price' => 'Price',
            'bonus_manager' => 'BonusManager',
            'data' => 'Data',
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
    public function getItem()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersItemsHandings()
    {
        return $this->hasMany(OrdersItemsHanding::className(), ['orders_items_id' => 'id']);
    }
}
