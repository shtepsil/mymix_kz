<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "history_bonus".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property integer $sum
 * @property integer $created_at
 *
 * @property User $user
 */
class HistoryBonus extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'history_bonus';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'sum', 'created_at'], 'required'],
            [['user_id', 'sum', 'created_at'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'name' => 'Название пополнения',
            'sum' => 'Сумма',
            'created_at' => 'Дата создания',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
