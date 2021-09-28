<?php
namespace frontend\form;

use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class Login extends Model
{
    public $login;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['login', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError('password', 'Неверный пароль или логин');
                $this->addError('login', 'Неверный пароль или логин');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'login' => 'E-mail',
            'password' => 'Пароль',
        ];
    }
    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->login);
        }
        return $this->_user;
    }
    public function send()
    {
        $result = [];
        if($this->login()){
            $result['message']['success'] = 'Вы успешно вошли!';
            $result['js']=<<<JS
location.reload();
JS;
        }else{
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
        }
        return $result;
    }
}
