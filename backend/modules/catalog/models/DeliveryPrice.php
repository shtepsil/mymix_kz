<?php

namespace backend\modules\catalog\models;

use backend\models\Settings;
use common\models\Delivery;
use shadow\widgets\CKEditor;
use yii;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * This is the model class for table "delivery_price".
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property double $price_kg
 * @property string $time
 * @property double $min_kg
 * @property string $pickup
 * @property integer $isOnlyPickup
 * @property string $delivery_methods
 * @property string $zip
 * @property float $delivery_method_courier_1_price
 * @property float $delivery_method_courier_1_free_sum
 * @property float $delivery_method_courier_1_min_sum
 * @property string $delivery_method_courier_1_days
 * @property string $delivery_method_courier_1_text
 * @property float $delivery_method_courier_2_price
 * @property float $delivery_method_courier_2_free_sum
 * @property float $delivery_method_courier_2_min_sum
 * @property string $delivery_method_courier_2_days
 * @property string $delivery_method_courier_2_text
 * @property float $delivery_method_courier_3_price
 * @property float $delivery_method_courier_3_free_sum
 * @property float $delivery_method_courier_3_max_sum
 * @property string $delivery_method_courier_3_days
 * @property string $delivery_method_courier_3_text
 * @property string $delivery_method_pickup_text
 *
 * @property OurStores[] $ourStores
 */
class DeliveryPrice extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'delivery_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['price_kg', 'min_kg'], 'number'],
            [['name', 'phone', 'time', 'zip'], 'string', 'max' => 255],
            ['isOnlyPickup', 'default', 'value' => 0],
            [['isOnlyPickup', 'delivery_method_courier_1_price', 'delivery_method_courier_1_free_sum', 'delivery_method_courier_2_price', 'delivery_method_courier_2_free_sum', 'delivery_method_courier_1_min_sum', 'delivery_method_courier_2_min_sum', 'delivery_method_courier_3_price', 'delivery_method_courier_3_free_sum', 'delivery_method_courier_3_max_sum'], 'integer'],
            [['pickup', 'delivery_method_courier_1_text', 'delivery_method_courier_2_text', 'delivery_method_courier_3_text', 'delivery_method_pickup_text'], 'string'],
            [['delivery_method_courier_1_days', 'delivery_method_courier_2_days', 'delivery_method_courier_3_days'], 'safe'],
            ['delivery_methods', 'jsonType']
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->delivery_methods = ($this->delivery_methods ? Json::encode($this->delivery_methods) : Json::encode([]));

            return true;
        }

        return false;
    }

    public function afterFind(){
        parent::afterFind();

        $this->delivery_methods = ($this->delivery_methods ? Json::decode($this->delivery_methods) : []);
    }

    public function jsonType($attribute, $params)
    {
        if (!empty($this->delivery_methods) && !Json::encode($this->delivery_methods)) {
            $this->addError($attribute, 'Wrong json text.');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOurStores()
    {
        return $this->hasMany(OurStores::className(), ['delivery_price_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'phone' => 'Телефон',
            'price_kg' => 'Цена за кг',
            'time' => 'Срок',
            'min_kg' => 'Мин. кг',
            'pickup' => 'Место самовывоза',
            'isOnlyPickup' => 'Только самовывоз',
            'delivery_methods' => 'Способы доставки',
            'zip' => 'Почтовый индекс',
            'delivery_method_courier_1_price' => 'Стоимость доставки (курьер 1)',
            'delivery_method_courier_1_free_sum' => 'Бесплатная доставка при стоимости от (курьер 1)',
            'delivery_method_courier_1_min_sum' => 'Минимальная сумма заказа для доставки (курьер 1)',
            'delivery_method_courier_1_days' => 'Количество дней (курьер 1)',
            'delivery_method_courier_1_text' => 'Доставка курьером 1 (текст)',
            'delivery_method_courier_2_price' => 'Стоимость доставки (курьер 2)',
            'delivery_method_courier_2_free_sum' => 'Бесплатная доставка при стоимости от (курьер 2)',
            'delivery_method_courier_2_min_sum' => 'Минимальная сумма заказа для доставки (курьер 2)',
            'delivery_method_courier_2_days' => 'Количество дней (курьер 2)',
            'delivery_method_courier_2_text' => 'Доставка курьером 2 (текст)',
            'delivery_method_courier_3_price' => 'Стоимость доставки (курьер 3 - бесплатная доставка)',
            'delivery_method_courier_3_free_sum' => 'Бесплатная доставка при стоимости от (курьер 3 - бесплатная доставка)',
            'delivery_method_courier_3_max_sum' => 'Максимальная сумма заказа для доставки (курьер 3 - бесплатная доставка)',
            'delivery_method_courier_3_days' => 'Количество дней (курьер 3 - бесплатная доставка)',
            'delivery_method_courier_3_text' => 'Доставка курьером 3 (текст - бесплатная доставка)',
            'delivery_method_pickup_text' => 'Самовывоз (текст)',
        ];
    }

    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }

        $controller_name = Inflector::camel2id(Yii::$app->controller->id);

        $result = [
            'form_action' => [$controller_name . '/save'],
            'cancel' => [$controller_name . '/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                        'name' => [],
                        'phone' => []
                    ]
                ],
                'info' => [
                    'title' => 'Информация о доставке',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                        'pickup' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className(),
                            ],
                        ],
                        'zip' => [],
                        'delivery_method_courier_1_text' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className(),
                            ],
                        ],
                        'delivery_method_courier_2_text' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className(),
                            ],
                        ],
                        'delivery_method_courier_3_text' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className(),
                            ],
                        ],
                        'delivery_method_pickup_text' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className(),
                            ],
                        ]
                    ]
                ],
                'cost' => [
                    'title' => 'Стоимость доставки',
                    'icon' => 'truck',
                    'options' => [],
                    'fields' => [
                        'price_kg' => [],
                        'time' => [],
                        'min_kg' => [],
                        'delivery_method_courier_1_price' => [],
                        'delivery_method_courier_1_free_sum' => [],
                        'delivery_method_courier_1_min_sum' => [],
                        'delivery_method_courier_1_days' => [
                            'type' => 'string'
                        ],
                        'delivery_method_courier_2_price' => [],
                        'delivery_method_courier_2_free_sum' => [],
                        'delivery_method_courier_2_min_sum' => [],
                        'delivery_method_courier_2_days' => [
                            'type' => 'string'
                        ],
                        'delivery_method_courier_3_price' => [],
                        'delivery_method_courier_3_free_sum' => [],
                        'delivery_method_courier_3_max_sum' => [],
                        'delivery_method_courier_3_days' => [
                            'type' => 'string'
                        ],
                        /*'isOnlyPickup' => [
                            'type' => 'checkbox'
                        ]*/
                    ]
                ],
                'methods' => [
                    'title' => 'Способы доставки',
                    'icon' => 'truck',
                    'options' => [],
                    'fields' => [
                        'delivery_methods' => [
                            'type' => 'checkboxList',
                            'data' => $this->getDeliveriesList()
                        ]
                    ]
                ]
            ]
        ];
        return $result;
    }

    protected function getDeliveriesList()
    {
        $settings = Settings::find()
            ->where(['group' => 'delivery'])
            ->andWhere(['like', 'key', 'delivery_method_'])
            ->select(['key', 'value'])
            ->all();

        $result = [];

        $list = Delivery::getDeliveriesFullName();

        if (!empty($settings)) {
            foreach ($settings as $s) {
                if (!empty($list[$s['key']])) {
                    $result[$s['key']] = $list[$s['key']];
                }
            }
        }

        return $result;
    }
}