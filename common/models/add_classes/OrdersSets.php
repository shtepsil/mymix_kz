<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_sets".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $set_id
 * @property integer $count
 * @property integer $price
 * @property integer $purch_price
 * @property string $bonus_manager
 * @property string $date_items
 *
 * @property Orders $order
 * @property Sets $set
 */
class OrdersSets extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_sets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'set_id', 'count', 'price', 'purch_price'], 'required'],
            [['order_id', 'set_id', 'count', 'price', 'purch_price'], 'integer'],
            [['bonus_manager'], 'number']
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
            'set_id' => 'Set ID',
            'count' => 'Count',
            'price' => 'Price',
            'purch_price' => 'Purch Price',
            'bonus_manager' => 'Bonus Manager',
            'date_items' => 'Товары',
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
    public function getSet()
    {
        return $this->hasOne(Sets::className(), ['id' => 'set_id']);
    }
}
