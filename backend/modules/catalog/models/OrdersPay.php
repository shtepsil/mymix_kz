<?php
namespace backend\modules\catalog\models;

use backend\models\SUser;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "orders_history".
 *
 * @property integer $id
 * @property integer $order_id
 * @property float $amount
 * @property float $real_amount
 * @property integer $status
 *
 * @property Orders $order
 */
class OrdersPay extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_pay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'amount'], 'required'],
            [['order_id', 'amount'], 'integer'],
            ['amount','safe']
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
            'amount' => 'Сумма',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::className(), ['id' => 'order_id']);
    }
}
