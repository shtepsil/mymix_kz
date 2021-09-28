<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_rollback_sets".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $set_order_id
 * @property double $count
 *
 * @property Orders $order
 * @property OrdersSets $setOrder
 */
class OrdersRollbackSets extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_rollback_sets';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'set_order_id', 'count'], 'required'],
            [['order_id', 'set_order_id'], 'integer'],
            [['count'], 'number']
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
            'set_order_id' => 'Товар',
            'count' => 'Количество',
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
    public function getSetOrder()
    {
        return $this->hasOne(OrdersSets::className(), ['id' => 'set_order_id']);
    }
}
