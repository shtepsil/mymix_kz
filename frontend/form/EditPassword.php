<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 04.09.15
 * Time: 11:12
 */
namespace frontend\form;

use yii\base\Model;

class EditPassword extends Model
{
    public $password1;
    public $password2;

    /**
     * Returns the form name that this model class should use.
     *
     * The form name is mainly used by [[\yii\widgets\ActiveForm]] to determine how to name
     * the input fields for the attributes in a model. If the form name is "A" and an attribute
     * name is "b", then the corresponding input name would be "A[b]". If the form name is
     * an empty string, then the input name would be "b".
     *
     * By default, this method returns the model class name (without the namespace part)
     * as the form name. You may override it when the model is used in different forms.
     *
     * @return string the form name of this model class.
     */
    public function formName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password1', 'password2'], 'required'],
            ['password2', 'compare','compareAttribute'=>'password1'],
            [
                ['password1','password2'],
                'match', 'pattern' => '/^[A-Za-z0-9_!@#$%^&*()+=?.,]+$/u',
                'message' => 'Не допустимые символы',
            ],
            [['password1','password2'],'string', 'length' => [6, 255]],

        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password1'=>'Новый пароль',
            'password2'=>'Повторите пароль',
        ];
    }
    public function send()
    {
        /**
         * @var $user \common\models\User
         */
        $result = [];
        if(\Yii::$app->user->isGuest){
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
            return $result;
        }
        $user = \Yii::$app->user->identity;
        $user->setPassword($this->password1);
        if($user->save(false)){
            $result['message']['success'] = 'Успешно сохранено!';
//            $result['js']=<<<JS
//location.reload();
//JS;

        }else{
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
        }
        return $result;
    }
}