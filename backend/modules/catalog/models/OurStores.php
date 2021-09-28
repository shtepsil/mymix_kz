<?php

namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "our_stores".
 *
 * @property integer $id
 * @property integer $delivery_price_id
 * @property string $name
 * @property string $name_pickup
 * @property string $y
 * @property string $x
 * @property integer $isVisible
 *
 * @property DeliveryPrice $deliveryPrice
 */
class OurStores extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'our_stores';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delivery_price_id', 'isVisible'], 'integer'],
            [['delivery_price_id', 'name', 'name_pickup'], 'required'],
            [['name', 'y', 'x', 'name_pickup'], 'string', 'max' => 255],
            ['delivery_price_id', 'exist', 'skipOnError' => true, 'targetClass' => DeliveryPrice::className(), 'targetAttribute' => ['delivery_price_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'delivery_price_id' => 'Город',
            'name' => 'Данные',
            'y' => 'Широта',
            'x' => 'Долгота',
            'isVisible' => 'Видимость',
            'name_pickup' => 'Название'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryPrice()
    {
        return $this->hasOne(DeliveryPrice::className(), ['id' => 'delivery_price_id']);
    }

    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'delivery_price_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => DeliveryPrice::className(),
                    'query' => [
                        'orderBy' => ['name' => SORT_ASC]
                    ]
                ]
            ],
            'name_pickup' => [],
            'name' => [],
            'y' => [],
            'x' => []
        ];
        $result = [
            'form_action' => [$controller_name . '/save'],
            'cancel' => [$controller_name . '/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields
                ]
            ]
        ];
        return $result;
    }
}