<?php

namespace frontend\form;

use common\models\User;
use yii\base\Model;
use yii\helpers\Json;

class EditLk extends Model
{
    public $name;
    public $phone;
    public $email;
    public $sex;
    public $dob;

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
            [['name', 'email', 'phone'], 'trim'],
            [['name', 'email', 'phone'], 'required'],
            [['phone'],'match','pattern' => '/^((\+?7)(\(?\d{3})\)-?)?(\d{3})(-?\d{4})$/','message'=>\Yii::t('main','Некорректный формат поля {attribute}')],
            ['phone', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'phone', 'filter' => ['<>', 'id', \Yii::$app->user->id]],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'email', 'filter' => ['<>', 'id', \Yii::$app->user->id]],
            [['sex'], 'integer'],
            [['sex'], 'in', 'range' => [1, 2]],
            ['dob', 'match', 'pattern' => "/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i"],
            ['dob', 'date', 'timestampAttribute' => 'dob', 'format' => 'dd/MM/yyyy'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Имя, фамилия',
            'phone' => 'Телефон',
            'email' => 'E-Mail',
            'dob' => 'Дата рождения',
        ];
    }
    public function send()
    {
        /**
         * @var $record \common\models\User
         */
        $result = [];
        if (\Yii::$app->user->isGuest) {
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
            return $result;
        }
        $record = \Yii::$app->user->identity;
//        if($record->isEntity==1){
//            $entity = [
//                'entity_name' => $this->entity_name,
//                'entity_address' => $this->entity_address,
//                'entity_bin' => $this->entity_bin,
//                'entity_iik' => $this->entity_iik,
//                'entity_bank' => $this->entity_bank,
//                'entity_bik' => $this->entity_bik,
//                'entity_nds' => ($this->entity_nds)?1:0,
//            ];
//            $record->data = Json::encode($entity);
//        }
//        $record->isEntity = $this->isEntity;
        $record->email = $this->email;
        $record->phone = $this->phone;
        $record->username = $this->name;
        $record->dob = $this->dob;
        if ($this->sex) {
            $record->sex = $this->sex;
        }
        if ($record->save(false)) {
            $result['message']['success'] = 'Успешно изменено!';
//            $result['js'] = <<<JS
//location.reload();
//JS;
        } else {
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
        }
        return $result;
    }
}