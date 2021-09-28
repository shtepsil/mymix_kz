<?php
namespace common\models;

use app\models\Auth;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\Orders;
use shadow\helpers\StringHelper;
use shadow\SActiveRecord;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $isSubscription
 * @property integer $isNotification
 * @property integer $sex
 * @property integer $dob
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $isEntity
 * @property integer $isWholesale
 * @property float $bonus
 * @property string $password write-only password
 * @property string $subs_email
 * @property string $data JSON
 * @property string $phone
 * @property string $code
 * @property integer $order_sum
 * @property double $discount
 * @property double $manager_id
 * @property integer $city_id
 *
 * @property DeliveryPrice $city
 * @property UserAddress[] $userAddresses
 * @property HistoryBonus[] $historyBonuses
 * @property UserInvited[] $userInviteds
 * @property UserInvited[] $userInviteds0
 * @property Orders[] $userOrders
 * @property Orders $lastUserOrder
 */
class User extends SActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            [['isEntity', 'bonus', 'order_sum', 'isWholesale'], 'default', 'value' => 0],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $email
     * @return static|null
     */
    public static function findByUsername($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuths()
    {
        return $this->hasMany(Auth::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAddresses()
    {
        return $this->hasMany(UserAddress::className(), ['user_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistoryBonuses()
    {
        return $this->hasMany(HistoryBonus::className(), ['user_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserInviteds()
    {
        return $this->hasMany(UserInvited::className(), ['user_invited' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserInviteds0()
    {
        return $this->hasMany(UserInvited::className(), ['user_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserOrders()
    {
        return $this->hasMany(Orders::className(), ['user_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastUserOrder()
    {
        return $this->hasOne(Orders::className(), ['user_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(DeliveryPrice::className(), ['id' => 'city_id']);
    }
    public $data_sex = [
        1 => 'Я — мужчина',
        2 => 'Я — женщина',
    ];
    public $all_users = [];
    public function generateCode($int = 0)
    {
        if ($int == 0) {
            $int = $this->created_at;
        }
        if (!$this->all_users) {
            $this->all_users = User::find()->indexBy('code')->all();
        }
        $code = StringHelper::num2alpha($int);
        if (isset($this->all_users[$code])) {
            $code = $this->generateCode($int - $this->id);
        }
        return $code;
    }
    public static function checkPhone($phone, $attributes)
    {
        $user = User::find()->andWhere(['phone' => $phone])->one();
        if (!$user) {
            $user = new User();
            $user->phone = $phone;
            $user->setAttributes($attributes, false);
            $user->status = $user::STATUS_ACTIVE;
            $user->password = \Yii::$app->security->generateRandomString(6);
            $user->generateAuthKey();
            if (!$user->save(false)) {
                $user = null;
            }
        }
        return $user;
    }
    public static function export($items)
    {
        $city_all = DeliveryPrice::find()->indexBy('id')->all();
        $columns = [
            [
                'attribute' => 'username',
                'header' => 'ФИО',
                'format' => 'text',
                'value' => function ($model) {
                    return ($model->username) ? $model->username : '';
                },
            ],
            [
                'attribute' => 'phone',
                'header' => 'Телефон',
                'format' => 'text',
                'value' => function ($model) {
                    return ($model->phone) ? $model->phone : '';
                },
            ],
            [
                'attribute' => 'city_id',
                'header' => 'Город',
                'format' => 'text',
                'value' => function ($model) use ($city_all) {
                    return (isset($city_all[$model->city_id]) ? $city_all[$model->city_id]->name : 'Не выбран');
                },
            ],
            [
                'attribute' => 'email',
                'header' => 'E-Mail',
                'format' => 'text',
                'value' => function ($model) {
                    return ($model->email) ? $model->email : '';
                },
            ],
            [
                'attribute' => 'isWholesale',
                'header' => 'Статус',
                'format' => 'text',
                'value' => function ($model) {
                    return ($model->isWholesale == 1) ? 'Оптовый' : 'Розничный';
                },
            ],
            [
                'attribute' => 'order_sum',
                'header' => 'Сумма заказов',
                'format' => 'text',
                'value' => function ($model) {
                    return number_format($model->order_sum, 0, '', ' ');
                },
            ],
            [
                'attribute' => 'bonus',
                'header' => 'Сумма бонусов',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var User $model */
                    return ($model->bonus) ? $model->bonus : 0;
                },
            ],
            [
                'attribute' => 'order_sum',
                'header' => 'Процент с заказа',
                'format' => 'text',
                'value' => function ($model) {
                    return Yii::$app->function_system->percent($model->id) . '%';
                },
            ],
            [
                'attribute' => 'discount',
                'header' => 'Скидка',
                'format' => 'text',
                'value' => function ($model) {
                    return ($model->discount ? ($model->discount . '%') : '');
                },
            ],
            [
                'header' => 'Последний заказ',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var User $model */
                    $orders = $model->lastUserOrder;
                    if ($orders) {
                        /**@var Orders $order */
                        $order = $orders;
                        return date('d.m.Y', $order->created_at);
                    } else {
                        return '';
                    }
                },
            ],
            [
                'header' => 'Статус',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var User $model */
                    return ($model->status == $model::STATUS_ACTIVE) ? 'Активирован' : 'Не активирован';
                },
            ],
        ];
        \moonland\phpexcel\Excel::export([
            'fileName' => 'export_users',
            'models' => $items,
            'columns' => $columns,
//            'savePath' => Yii::getAlias('@frontend/tmp'),
//            'asAttachment' => false
        ]);
    }
}
