<?php
namespace common\models;

use shadow\SActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "subscriptions".
 *
 * @property integer $id
 * @property string $email
 * @property integer $created_at
 * @property integer $updated_at
 */
class Subscriptions extends SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'subscriptions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            ['email', 'unique', 'targetClass' => Subscriptions::className(), 'targetAttribute' => 'email'],
            [['email'], 'email']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'E-Mail',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
}
