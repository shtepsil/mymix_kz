<?php
namespace backend\models;

use shadow\SActiveRecord;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * SUser model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class SUser extends SActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%s_user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
//            [
//                'class' => '\shadow\behaviors\UploadFileBehavior',
//                'attributes' => ['img'],
//                'filePath' => '@web_frontend/uploads/managers/[[pk]]_[[attribute]].[[extension]]',
//                'fileUrl' => '/uploads/managers/[[pk]]_[[attribute]].[[extension]]',
//            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'new_password'], 'trim'],
            [['email'], 'required'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [
                ['new_password'],
                'match',
                'pattern' => '/^[A-Za-z0-9_!@#$%^&*()+=?.,]+$/u',
                'message' => 'Не допустимые символы',
            ],
            [['new_password'], 'string', 'length' => [4, 255]],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => SUser::className(), 'targetAttribute' => 'email'],
            [['username'], 'safe'],
            [['role'], 'safe', 'on' => 'admin']
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
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['email' => $username, 'status' => self::STATUS_ACTIVE]);
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
     * This method is invoked before deleting a record.
     * The default implementation raises the [[EVENT_BEFORE_DELETE]] event.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeDelete()
     * {
     *     if (parent::beforeDelete()) {
     *         // ...custom code here...
     *         return true;
     *     } else {
     *         return false;
     *     }
     * }
     * ```
     *
     * @return boolean whether the record should be deleted. Defaults to true.
     */
    public function beforeDelete()
    {
        $auth = Yii::$app->authManager;
        $auth->revokeAll($this->id);
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }
    /**
     * This method is called at the beginning of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is true,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is false.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (parent::beforeSave($insert)) {
     *         // ...custom code here...
     *         return true;
     *     } else {
     *         return false;
     *     }
     * }
     * ```
     *
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @return boolean whether the insertion or updating should continue.
     * If false, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert)
    {
        if ($this->new_password) {
            $this->password = $this->new_password;
        }
        if ($this->isNewRecord) {
            $this->generateAuthKey();
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }
    /**
     * This method is called at the end of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is true,
     * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is false. The event class used is [[AfterSaveEvent]].
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     */
    public function afterSave($insert, $changedAttributes)
    {
        /**
         * @var $auth \yii\rbac\PhpManager
         */
        $auth = Yii::$app->authManager;
        if ($this->isNewRecord) {
            $role = $auth->getItem($this->role);
            if ($role) {
                $auth->assign($role, $this->id);
            }
        } else {
            $user_roles = $auth->getRolesByUser($this->id);
            if (!isset($user_roles[$this->role])) {
                $auth->revokeAll($this->id);
                $role = $auth->getItem($this->role);
                if ($role) {
                    $auth->assign($role, $this->id);
                }
            }
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Имя',
            'email' => 'E-Mail',
            'new_password' => 'Новый пароль',
            'role' => 'Роль',
        ];
    }
    public $new_password;
//    public $role;
    public function FormParams()
    {
        $action = 's-users';
        $auth = Yii::$app->authManager;
        $rules = $auth->getRoles();
        $data_rules = [];
        foreach ($rules as $role) {
            $data_rules[$role->name] = $role->description;
        }
        $fields = [
            'role' => [
                'type' => 'dropDownList',
                'data' => $data_rules,
            ],
            'username' => [],
            'email' => [],
            'new_password' => [
                'type' => 'password'
            ],
        ];
        if (!Yii::$app->user->can('admin')) {
            unset($fields['role']);
        } else {
        }
        $result = [
            'form_action' => [$action . '/save'],
            'cancel' => ['site/' . $action],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields,
                ],
            ]
        ];
//        if (!$this->isNewRecord) {
//            $user_roles = $auth->getRolesByUser($this->id);
//            if($user_roles){
//                $this->role = array_keys($user_roles);
//            }
//        }
        return $result;
    }
}
