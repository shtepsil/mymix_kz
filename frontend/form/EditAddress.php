<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 28.08.15
 * Time: 15:56
 */
namespace frontend\form;

use common\models\UserAddress;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

class EditAddress extends Model
{
    public $id;
    public $city;
    public $street;
    public $home;
    public $house;
    public $phone;
    public $isMain;

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
            [['street', 'home'], 'trim'],
            [['city', 'street', 'home','phone'], 'required'],
            [['phone'],'match','pattern' => '/^((\+?7)(\(?\d{3})\)-?)?(\d{3})(-?\d{4})$/','message'=>\Yii::t('main','Некорректный формат поля {attribute}')],
            [['city'], 'integer'],
            [['street', 'home','house'], 'string', 'max' => 255],
            [['city', 'street','home','house','id','phone','isMain'], 'safe'],

        ];
    }
    public $data_city = [
        1 => 'Алматы',
//        2 => 'Астана'
    ];
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'city'=>'Город',
            'street'=>'Улица',
            'home'=>'Дом',
            'house'=>'Квартира',
            'phone'=>'Телефон',
            'isMain'=>'Сделать основным адресом доставки',
        ];
    }
    public function send()
    {
        /**
         * @var $record \common\models\UserAddress
         * @var $user \common\models\User
         */
        $result = [];
        if(\Yii::$app->user->isGuest){
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
            return $result;
        }
        $attrs = $this->attributes;
        $user = \Yii::$app->user->identity;
        $address_user = $user->getUserAddresses()->indexBy('id')->all();

        if($this->id&&isset($address_user[$this->id])){
            $record = $address_user[$this->id];
        }else{
            $record = new UserAddress();
            $record->user_id = $user->id;
            unset($attrs['id']);
        }
//        $record->isMain = ($this->isMain) ? 1 : 0;
        if($this->isMain==1){
            UserAddress::updateAll(['isMain' => 0], ['user_id' => $user->id]);
        }
        $attrs = ArrayHelper::htmlEncode($attrs);
        $record->setAttributes($attrs,false);
        if($record->save(false)){
            $url = Json::encode(Url::to(['user/address']));
            $result['message']['success'] = 'Успешно сохранено!';
            $result['js']=<<<JS
location={$url};
JS;

        }else{
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
        }
        return $result;
    }
}