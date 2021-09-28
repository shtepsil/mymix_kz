<?php

namespace backend\modules\catalog\models;

use yii;
use yii\db\ActiveQuery;
use yii\helpers\Inflector;
use yii\helpers\Json;

class Sales extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sales';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basket_sum_from', 'gifts_count', 'priority'], 'integer'],
            ['priority', 'default', 'value' => 100],
            [['value'], 'double'],
            [['name'], 'required'],
            [['name', 'type_value'], 'string', 'max' => 255],
            ['active', 'boolean'],
            ['type_value', 'in', 'range' => array_keys(self::$valueType)],
            [['goods'], 'filter', 'filter' => [$this, 'validateGoods']],
            [['gifts'], 'filter', 'filter' => [$this, 'validateGifts']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'active' => 'Активность',
            'value' => 'Скидка',
            'type_value' => 'Тип скидки',
            'goods' => 'Список товаров',
            'basket_sum_from' => 'Сумма заказа в корзине от',
            'gifts' => 'Список подарков',
            'gifts_count' => 'Количество подарков',
            'priority' => 'Приоритет (чем меньше, тем выше)'
        ];
    }

    public static $valueType = [
        'percent' => 'Процентная',
        'number' => 'Числовая'
    ];

    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }

        $controller_name = Inflector::camel2id(\Yii::$app->controller->id);

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
                        'active' => [
                            'type' => 'checkbox'
                        ],
                        'priority' => [],
                        'type_value' => [
                            'type' => 'dropDownList',
                            'data' => self::$valueType,
                            'params' => [
                                'prompt' => 'Выберите...'
                            ]
                        ],
                        'value' => [],
                        'basket_sum_from' => []
                    ]
                ],
                'info' => [
                    'title' => 'Товары',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                        'goods' => [
                            'type' => 'multipleInput',
                            'columns' => [
                                [
                                    'name'  => 'name',
                                    'enableError' => true,
                                    'options' => [
                                        'placeholder' => 'Наименование',
                                        'class' => 'goods'
                                    ]
                                ],
                                [
                                    'name'  => 'count',
                                    'enableError' => true,
                                    'options' => [
                                        'placeholder' => 'Количество'
                                    ]
                                ],
                                [
                                    'name'  => 'id',
                                    'options' => [
                                        'class' => 'hidden'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'cost' => [
                    'title' => 'Подарки',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                        'gifts_count' => [],
                        'gifts' => [
                            'type' => 'multipleInput',
                            'columns' => [
                                [
                                    'name'  => 'name',
                                    'enableError' => true,
                                    'options' => [
                                        'placeholder' => 'Наименование',
                                        'class' => 'goods'
                                    ]
                                ],
                                [
                                    'name'  => 'price',
                                    'enableError' => true,
                                    'options' => [
                                        'placeholder' => 'Стоимость',
                                    ]
                                ],
                                [
                                    'name'  => 'id',
                                    'options' => [
                                        'class' => 'hidden'
                                    ]
                                ]
                            ],
                            'max' => 5
                        ]
                    ]
                ],
            ]
        ];

        return $result;
    }

    public function afterFind(){
        parent::afterFind();

        $this->goods = (!empty($this->goods) ? Json::decode($this->goods) : [
            [
                'id' => null,
                'count' => 0,
                'name' => ''
            ]
        ]);

        $this->gifts = (!empty($this->gifts) ? Json::decode($this->gifts) : [
            [
                'id' => null,
                'price' => 0,
                'name' => ''
            ]
        ]);
    }

    public function validateGoods ($value) {
        if (!empty($value)) {
            try {
                $model = new yii\base\DynamicModel(['name', 'count', 'id']);

                foreach ($value as $key => $v) {
                    if (current(array_unique($v)) == '') {
                        if (count($value) == 1) {
                            return null;
                        } else {
                            unset($value[$key]);
                        }
                    }

                    $model->name = $v['name'];
                    $model->count = $v['count'];
                    $model->id = $v['id'];

                    $model->addRule(['name', 'count', 'id'], 'required',
                        ['message' => 'Все поля у ' . self::getAttributeLabel('goods') . ' должны быть заполнены.'])
                        ->addRule(['name'], 'string')
                        ->addRule(['count', 'id'], 'integer', ['message' => 'Число товаров должно быть целым.'])
                        ->validate();

                    if ($model->hasErrors()) {
                        $error = $model->getFirstErrors();
                        $k = 'goods[' . $key . '][' . key($error) . ']';
                        $this->addError($k, current($error));

                        return null;
                    }
                }

                return \yii\helpers\Json::encode($value);
            } catch (\yii\base\InvalidParamException $e) {
                $this->addError('goods', $e->getMessage());
                return null;
            }
        }
    }

    public function validateGifts ($value) {
        if (!empty($value)) {
            try {
                $model = new yii\base\DynamicModel(['name', 'price', 'id']);

                foreach ($value as $key => $v) {
                    if (current(array_unique($v)) == '') {
                        if (count($value) == 1) {
                            return null;
                        } else {
                            unset($value[$key]);
                        }
                    }

                    $model->name = $v['name'];
                    $model->price = $v['price'];
                    $model->id = $v['id'];

                    $model->addRule(['name', 'price', 'id'], 'required',
                        ['message' => 'Все поля у ' . self::getAttributeLabel('goods') . ' должны быть заполнены.'])
                        ->addRule(['name'], 'string')
                        ->addRule(['id'], 'integer', ['message' => 'Число товаров должно быть целым.'])
                        ->addRule(['price'], 'double', ['message' => 'Цена должна быть числом.'])
                        ->validate();

                    if ($model->hasErrors()) {
                        $error = $model->getFirstErrors();
                        $k = 'goods[' . $key . '][' . key($error) . ']';
                        $this->addError($k, current($error));

                        return null;
                    }
                }

                return \yii\helpers\Json::encode($value);
            } catch (\yii\base\InvalidParamException $e) {
                $this->addError('gifts', $e->getMessage());
                return null;
            }
        }
    }

    /**
     * @param array $items
     * @return array
     */
    public function getGifts(array $items)
    {
        $basketInfo = $this->getBasketSum($items);
        $sql = self::find();

        foreach ($items as $key => $item) {
            if ((int)$key > 0) {
                $sql->orWhere(new yii\db\Expression('JSON_CONTAINS(goods, \'{"id":"'.(int)$key.'"}\')'));
            }
        }

        $result = [];
        $res = $sql
            ->orWhere([
                'AND',
                ['goods' => null],
                ['>', 'basket_sum_from', 0]
            ])
            ->andWhere(['not', ['gifts' => null]])
            ->andWhere(['active' => 1])
            ->all();

        if (!empty($res)) {
            $ids = [];
            $sales = [];

            foreach ($res as $r) {
                $i = 0;
                foreach ($r->goods as $g) {
                    if (isset($items[$g['id']]) && $items[$g['id']] >= $g['count']) {
                        $i++;
                    }
                }

                $result[$r->id]['count'] = $r->gifts_count;
                $result[$r->id]['buttonActive'] = false;

                if (($i >= count($r->goods)) || ($r->basket_sum_from && $r->basket_sum_from > 0 && $basketInfo['sum'] >= $r->basket_sum_from)) {
                    $result[$r->id]['title'] = 'Вы можете выбрать себе '.(count($r->goods) > 1 ? 'подарки' : ' подарок');
                    $result[$r->id]['buttonActive'] = true;
                } elseif ($r->basket_sum_from && $r->basket_sum_from > 0) {
                    $result[$r->id]['title'] = 'Добавьте товары в корзину на сумму '.(number_format(($r->basket_sum_from - $basketInfo['sum']), 0, '', ' ')).' 〒, чтобы получить подарок';
                }
                else {
                    $result[$r->id]['title'] = 'Добавьте товары в корзину, чтобы получить подарок';
                }

                if ($r->gifts_count > 0) {
                    $i = 0;
                    foreach ($r->gifts as $g) {
                        if (isset($items[$g['id']])) {
                            $i++;
                        }
                        else {
                            $ids[] = $g['id'];
                            $sales[$r->id][$g['id']] = [
                                'id' => $g['id'],
                                'price' => $g['price']
                            ];
                        }
                    }

                    if ($i >= $r->gifts_count) {
                        unset($result[$r->id], $sales[$r->id]);
                    }
                }
            }

            if ($ids) {
                if ($products = Items::find()
                    ->select(['id', 'name', 'cid', 'price', 'img_list'])
                    ->where(['in', 'id', $ids])
                    ->andWhere(['isVisible' => 1])
                    ->andWhere(['status' => 1])
                    ->all()) {
                    foreach ($products as $product) {
                        foreach ($sales as $key => $sale) {
                            if (isset($sale[$product->id])) {
                                $result[$key]['items'][$product->id] = [
                                    'id' => $product->id,
                                    'cid' => $product->id,
                                    'categoryName' => $product->c->name,
                                    'name' => $product->name,
                                    'priceOld' => $product->real_price(),
                                    'price' => $sale[$product->id]['price'],
                                    'img' => $product->img(),
                                    'url' => $product->url()
                                ];
                            }
                        }
                    }
                }

            }
        }

        return $result;
    }

    /**
     * @param $items
     * @return array
     */
    public function getBasketSum($items)
    {
        /**
         * @var Items[] $db_items
         * @var Items $target_item
         */
        $result = [];
        $sum = 0;

        if ($items) {
            $q = new ActiveQuery(Items::className());
            $q->indexBy('id')
                ->andWhere(['id' => array_keys($items)]);
            $db_items = $q->all();

            if ($db_items) {
                /**
                 * @var $functions \frontend\components\FunctionComponent
                 */
                $functions = Yii::$app->function_system;
                if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
                    $discount = [];
                } else {
                    $discount = $functions->discount_sale_items($db_items, $items);
                }

                foreach ($db_items as $item_id => $item) {
                    $count = $items[$item_id];
                    $full_price_item = $functions->full_item_price($discount, $item, $count);
                    $sum += $full_price_item;
                }
            }
        }

        if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
            $order = new Orders(['discount' => Yii::$app->user->identity->discount . '%']);
            $sum = $sum - $order->discount($sum);
        }

        $result['sum'] = $sum;

        return $result;
    }

    /**
     * @param array $items
     * @return array
     */
    public function getSale(array $items)
    {
        $basketInfo = $this->getBasketSum($items);

        $result = [];
        $sale = self::find()
            ->where(['>', 'value', 0])
            ->andWhere(['goods' => null])
            ->andWhere(['gifts' => null])
            ->andWhere(['active' => 1])
            ->orderBy(['priority' => SORT_ASC])
            ->one();

        if (!empty($sale)) {
            if (empty($sale->basket_sum_from) || ($sale->basket_sum_from > 0 && $basketInfo['sum'] >= $sale->basket_sum_from)) {
                $result['value'] = $sale->value;
                $result['type'] = $sale->type_value;
            }
        }

        return $result;
    }

    public function checkOnAddGift(int $itemId)
    {
//        $gifts = Yii::$app->session->get('gifts', []);
        $gifts = Yii::$app->c_cookie->get('gifts', []);

        if (array_key_exists($itemId, $gifts)) {
            return true;
        }

        return false;
    }

    public function deleteGift(int $itemId)
    {
        $gifts = Yii::$app->session->get('gifts', []);

        if (!empty($gifts) && isset($gifts[$itemId])) {
            unset($gifts[$itemId]);

            Yii::$app->session->set('gifts', $gifts);
        }
    }
}