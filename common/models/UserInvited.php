<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_invited".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $user_invited
 * @property string  $email
 * @property integer $status
 * @property integer $order_id
 *
 * @property User    $userInvited
 * @property User    $user
 */
class UserInvited extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_invited';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'user_invited', 'status', 'order_id'], 'integer'],
            [['email'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'user_id'      => 'User ID',
            'user_invited' => 'User Invited',
            'email'        => 'Email',
            'status'       => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserInvited()
    {
        return $this->hasOne(User::className(), ['id' => 'user_invited']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
