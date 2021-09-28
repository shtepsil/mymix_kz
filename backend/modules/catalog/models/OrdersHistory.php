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
 * @property integer $user_id
 * @property string $user_name
 * @property string $action
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Orders $order
 * @property SUser $user
 */
class OrdersHistory extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'action'], 'required'],
            [['order_id', 'user_id', 'action'], 'integer'],
            [['user_name'], 'string', 'max' => 255],
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
            'user_id' => 'Пользователь',
            'user_name' => 'ФИО',
            'action' => 'Действие',
            'created_at' => 'Дата создания',
            'updated_at' => 'Updated At',
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
    public function getUser()
    {
        return $this->hasOne(SUser::className(), ['id' => 'user_id']);
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    public $data_action = [
        0 => 'Заказ изменён',
        1 => 'Заказ оформлен',
        2 => 'Заказ формирование',
        3 => 'Заказ подтверждён клиентом',
        4 => 'Заказ на подтвержение клиентом',
        5 => 'Заказ доставляется',
        6 => 'Заказ выполнен',
        7 => 'Частичный возврат',
        8 => 'Полный возврат',
        9 => 'Клиент не отвечает',
        11 => 'Отменил заказ',
        12 => 'Заказ обработан',
        13 => 'Заказ возобновлён',
        14 => 'Отказ клиента',
        15 => 'Принял заказ',
    ];
}
