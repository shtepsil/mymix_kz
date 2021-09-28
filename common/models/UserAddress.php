<?php

namespace common\models;

use Yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "user_address".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $city
 * @property string $street
 * @property string $home
 * @property string $house
 * @property string $phone
 * @property integer $isMain
 *
 * @property User $user
 * @property mixed data_city
 */
class UserAddress extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'street', 'home','phone'], 'required'],
            [['user_id', 'city','isMain'], 'integer'],
            [['isMain'],'default','value'=>0],
            [['street'], 'string', 'max' => 500],
            [['home', 'house'], 'string', 'max' => 255]
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
            'city' => 'Город',
            'street' => 'Улица',
            'home' => 'Дом',
            'house' => 'Кв',
            'phone' => 'Телефон',
            'isMain' => 'Основной',
        ];
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $result = [
            'form_action' => ["$controller_name/save"],
            'cancel' => ["$controller_name/index"],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                        'user_id' => [
                            'type' => 'dropDownList',
                            'data' => User::find()->select(['username', 'id'])->indexBy('id')->column(),
                        ],
                        'city' => [
                            'type' => 'dropDownList',
                            'data' => $this->data_city,
                        ],
                        'street' => [],
                        'home' => [],
                        'house' => [],
                        'phone' => [
                            'widget' => [
                                'class' => \yii\widgets\MaskedInput::className(),
                                'config' => [
                                    'mask' => '+7(999)-999-9999',
                                    'definitions' => [
                                        'maskSymbol' => '_'
                                    ],
                                ]
                            ]
                        ],
                    ],
                ]
            ]
        ];
        $user_id = Yii::$app->request->get('user_id');
        if ($user_id && $this->isNewRecord) {
            $this->user_id = $user_id;
        }
        return $result;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    private $_data_city = [];
    public function getData_city()
    {
        return Yii::$app->function_system->data_city;
    }
}
