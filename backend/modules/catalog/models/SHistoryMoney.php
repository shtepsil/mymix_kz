<?php

namespace backend\modules\catalog\models;

use backend\models\SUser;
use Yii;

/**
 * This is the model class for table "s_history_money".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $sum_order
 * @property integer $sum_purch
 * @property integer $sum_bonus
 * @property integer $date_created
 *
 * @property SUser $user
 */
class SHistoryMoney extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_history_money';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'sum_order', 'sum_purch', 'sum_bonus', 'date_created'], 'required'],
            [['id', 'user_id', 'sum_order', 'sum_purch', 'sum_bonus', 'date_created'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'sum_order' => 'Сумма заказа',
            'sum_purch' => 'Sum Purch',
            'sum_bonus' => 'Сумма бонуса',
            'date_created' => 'Дата создания',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(SUser::className(), ['id' => 'user_id']);
    }
}
