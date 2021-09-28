<?php

namespace backend\modules\catalog\models;

use common\components\Debugger as d;
use backend\models\SUser;
use common\components\ReferralSystem;
use common\models\Delivery;
use common\models\HistoryBonus;
use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the model class for table "orders".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $user_name
 * @property string $user_phone
 * @property string $user_mail
 * @property string $user_address
 * @property string $user_comments
 * @property string $city_id
 * @property integer $isEntity
 * @property integer $date_delivery
 * @property string $time_delivery
 * @property string $code
 * @property integer $full_price
 * @property integer $full_purch_price
 * @property string $discount
 * @property string $payment
 * @property integer $bonus_use
 * @property integer $bonus_add
 * @property integer $bonus_manager
 * @property integer $bonus_driver
 * @property integer $status
 * @property string $pay_status
 * @property integer $manager_id
 * @property integer $driver_id
 * @property integer $collector_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $price_delivery
 * @property string $id_1c
 * @property integer $enable_bonus
 * @property integer $isWholesale
 * @property integer $promo_code_id
 * @property string $admin_comments
 * @property integer $isPhoneOrder
 * @property string $delivery
 *
 * @property OrdersItems[] $ordersItems
 * @property OrdersComments[] $ordersComments
 * @property OrdersHistory[] $ordersHistories
 */
class Orders extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'full_price',
                    '!full_purch_price',
                    '!bonus_use',
                    'bonus_add',
                    '!status',
                    '!manager_id',
                    '!collector_id',
                    'price_delivery',
                    'isEntity',
                ],
                'integer',
            ],
            [['user_name', 'user_phone', 'user_address', 'payment'], 'required'],
            [['user_comments', 'delivery'], 'string'],
            [['user_name', 'user_phone', 'user_mail', 'time_delivery', 'code'], 'string', 'max' => 255],
            [['user_address'], 'string', 'max' => 500],
            [['payment'], 'string', 'max' => 50],
            [['date_delivery', 'admin_comments'], 'safe'],
            [['city_id', 'isWholesale', 'isPhoneOrder'], 'integer'],
            [['!full_price'], 'safe'],
            ['bonus_use', 'default', 'value' => 0],
            [['discount'], 'string', 'max' => 255, 'on' => ['admin']],
            [['enable_bonus'], 'integer', 'on' => ['admin']],
            [
                ['manager_id', 'driver_id', 'collector_id', 'id_1c', 'enable_bonus', 'bonus_use'],
                'safe',
                'on' => 'admin',
            ],
            [['driver_id'], 'integer', 'on' => 'collector'],
            [['driver_id', 'id_1c'], 'safe', 'on' => 'collector'],
            [['enable_bonus'], 'integer', 'on' => ['manager']],
            [['enable_bonus'], 'safe', 'on' => ['manager']],
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
            'user_name' => 'Имя',
            'user_phone' => 'Телефон',
            'user_mail' => 'E-Mail',
            'user_address' => 'Адрес',
            'user_comments' => 'Комментарий пользователя',
            'isEntity' => 'Юридическое лицо',
            'date_delivery' => 'Дата доставки/забора',
            'time_delivery' => 'Время доставки/забора',
            'code' => 'Промо код',
            'full_price' => 'Сумма заказа',
            'price_delivery' => 'Стоимость доставки',
            'payment' => 'Способ оплаты',
            'bonus_use' => 'Используемые бонусы',
            'bonus_add' => 'Бонус за заказ',
            'status' => 'Статус',
            'pay_status' => 'Статус онлайн платежа',
            'manager_id' => 'Менеджер',
            'driver_id' => 'Водитель',
            'collector_id' => 'Сборщик',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'isWholesale' => 'Оптовик',
            'id_1c' => 'Накладная №',
            'city_id' => 'Город',
            'enable_bonus' => 'Бонусы',
            'admin_comments' => 'Ком-рий админ, мен-жер',
            'isPhoneOrder' => 'Заказ по телефону',
            'delivery' => 'Способ доставки',
            'our_stories_id' => 'Пункт самовывоза'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersItems()
    {
        return $this->hasMany(OrdersItems::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersSets()
    {
        return $this->hasMany(OrdersSets::className(), ['order_id' => 'id']);
    }

    public function getData_payment()
    {

        return [
            0 => '',
            1 => \Yii::$app->settings->get('payment_type_cash'),
            2 => \Yii::$app->settings->get('payment_type_online'),
            3 => \Yii::$app->settings->get('payment_type_cards'),
        ];
    }

    /**
     * This method is invoked before validation starts.
     * The default implementation raises a `beforeValidate` event.
     * You may override this method to do preliminary checks before validation.
     * Make sure the parent implementation is invoked so that the event can be raised.
     * @return boolean whether the validation should be executed. Defaults to true.
     * If false is returned, the validation will stop and the model is considered invalid.
     */
    public function beforeValidate()
    {
        if (is_string($this->date_delivery)) {
            $this->date_delivery = strtotime($this->date_delivery);
        }

        return parent::beforeValidate(); // TODO: Change the autogenerated stub
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

    public $_all_users = [];

    public function getRow($attribute)
    {
        switch ($attribute) {
            case 'actions_model':
                $url = Url::to(['orders/deleted', 'id' => $this->id]);
                $url_edit = Url::to(['orders/control', 'id' => $this->id]);
                $result = <<<HTML
<a class="btn-default btn-xs" href="{$url_edit}"><i class="fa fa-pencil"></i></a>
<a href="{$url}" class="btn-danger btn-xs btn-confirm btn">
	<i class="fa fa-times fa-inverse"></i>
</a>
HTML;
                break;
            case 'user_name':
                $url = Url::to(['orders/control', 'id' => $this->id]);
                $result = <<<HTML
<a href="{$url}">
    {$this->user_name}
</a>
HTML;
                break;
            case 'status':
                $result = $this->data_status[$this->status];
                break;
            case 'pay_status_text':
                $result = (isset($this->data_pay_status[$this->pay_status]) ? $this->data_pay_status[$this->pay_status] : 0);
                break;
            case 'created_at':
                $result = date('d.m.y', $this->created_at);
                break;
            case 'date_delivery':
                if ($this->date_delivery) {
                    $result = date('d.m.y', $this->date_delivery);
                } else {
                    $result = '';
                }
                break;
            case 'full_price':
                $result = number_format($this->full_price, 0, ',', ' ');
                break;
            case 'performer':
                if (!$this->_all_users) {
                    $this->_all_users = SUser::find()->indexBy('id')->all();
                }
                $result = '';
                $id = 'manager_id';
                if (Yii::$app->user->identity->role == 'collector') {
                    $id = 'collector_id';
                } elseif (Yii::$app->user->identity->role == 'driver') {
                    $id = 'driver_id';
                } else {
                    if ($this->status == 0 || $this->status == 2) {
                        $id = 'manager_id';
                    } elseif ($this->status == 1) {
                        $id = 'collector_id';
                    } elseif (in_array($this->status, [3, 4, 6, 7])) {
                        $id = 'driver_id';
                    }
                }
                if (isset($this->{$id}) && isset($this->_all_users[$this->{$id}])) {
                    $result = $this->_all_users[$this->{$id}]->username;
                }
                break;
            case 'delivery':
                $deliveryMethods = Delivery::getDeliveriesName();
                $result = (!empty($this->delivery) ? $deliveryMethods[$this->delivery] : 'Не указан');
                break;
            default:
                $result = isset($this->{$attribute}) ? $this->{$attribute} : '';
                break;
        }

        return $result;
    }

    /**
     * @param $q \yii\db\ActiveQuery
     * @param $name
     * @param $value
     */
    public static function searchTimeRow(&$q, $name, $value)
    {
        if ($value !== '') {
            $array = explode('-', $value);
            $end = $start = false;
            list($start, $end) = $array;
            if (is_numeric($start)) {
                $date = \DateTime::createFromFormat(
                    'd/m/Y H:i:s', date('d/m/Y', $start / 1000) . ' 00:00:00', new \DateTimeZone(Yii::$app->timeZone)
                );
                $q->andWhere(['>=', '`orders`.' . $name, $date->getTimestamp()]);
            }
            if (is_numeric($end)) {
                $date = \DateTime::createFromFormat(
                    'd/m/Y H:i:s', date('d/m/Y', $end / 1000) . ' 23:59:59', new \DateTimeZone(Yii::$app->timeZone)
                );
                $q->andWhere(['<=', '`orders`.' . $name, $date->getTimestamp()]);
            }
        }
    }

    public function FormParams()
    {
        $main = [
            'manager_id' => [
                'relation' => [
                    'class' => 'common\models\User',
                    'label' => 'username',
                ],
            ],
            'driver_id' => [
                'relation' => [
                    'class' => 'common\models\User',
                    'label' => 'username',
                ],
            ],
            'collector_id' => [
                'relation' => [
                    'class' => 'common\models\User',
                    'label' => 'username',
                ],
            ],
            'status' => [
                'type' => 'dropDownList',
                'data' => $this->data_status,
            ],
            'payment' => [
                'type' => 'dropDownList',
                'data' => $this->data_payment,
            ],
            'user_name' => [],
            'user_phone' => [],
            'user_mail' => [],
            'user_address' => [],
            'user_comments' => [
                'type' => 'textArea',
            ],
            'date_delivery' => [],
            'time_delivery' => [],
            'delivery' => [
                'type' => 'dropDownList',
                'data' => Delivery::getDeliveriesName(),
            ],
            'code' => [],
            'full_price' => [],
            'bonus_use' => [],
            'bonus_add' => [],
        ];
        if ($this->isNewRecord) {
            $main['user_id'] = [
                'relation' => [
                    'class' => 'common\models\User',
                    'label' => 'username',
                ],
            ];
        }
        $result = [
            'form_action' => ['orders/save'],
            'cancel' => ['orders/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $main,
                ],
                'values' => [
                    'title' => 'Товары',
                    'icon' => 'th-list',
                    'options' => [],
                    'render' => [
                        'view' => 'items',
                        'data' => [
                            'order' => $this,
                        ],
                    ],
                ],
            ],
        ];

        return $result;
    }

    public $data_insert = [];

    public function UpdateOrderItems($items = [], $sets = [], $force = false)
    {
        /**
         * @var $order_item        OrdersItems
         * @var $target_item       Items
         * @var $old_type_handling OrdersItemsHanding[]
         */
        if (!$items && $force == false) {
            $items = Yii::$app->request->post('ordersItems');
        }
        if (!$sets && $force == false) {
            $sets = Yii::$app->request->post('ordersSets');
        }
        $insert = [];
        $insert_handing = [];
        $sum = 0;
        $full_purch_price = $full_bonus_manager = 0;
        $old_items = $this->getOrdersItems()->indexBy('item_id')->all();

        if ($items) {
            $db_items = Items::find()->indexBy('id')->where(['id' => array_keys($items)])->all();
            /**
             * @var $functions \frontend\components\FunctionComponent
             */
            $functions = Yii::$app->function_system;
            $sessions_items = $this->to_session_items($items);
            if (!$this->isNewRecord) {
                $db_items = $this->convert_all_to_model($old_items, $db_items);
            }
            if (!trim($this->discount)) {
                if ($this->isWholesale == 0) {
                    $discount = $functions->discount_sale_items($db_items, $sessions_items);
                } else {
                    $discount = [];
                }
            } else {
                $discount = [];
            }
            foreach ($items as $key => $value) {
                if (isset($old_items[$key])) {
                    $order_item = $old_items[$key];
                    $target_item = $db_items[$key];
                    if (isset($value['price'])) {
                        $real_price = $target_item->real_price();
                        $new_price = (int)$value['price'];
                        if ($real_price != $new_price) {
                            if ($target_item->discount) {
                                $target_item->discount = 0;
                            }
                            $target_item->price = $new_price;
                        }
                        $order_item->price = $value['price'];
                    } else {
                        if ($target_item->discount) {
                            $target_item->discount = 0;
                        }
                        $target_item->price = $order_item->price;
                    }
                    $order_item->count = $value['count'];
                    $order_item->weight = (isset($value['weight']) ? $value['weight'] : 0);
                    $full_item_price = $functions->full_item_price(
                        $discount, $target_item, $order_item->count, $order_item->weight
                    );
                    $sum += $full_item_price;
                    $full_purch_price += $target_item->sum_price($order_item->count);
//                    $full_bonus_manager += $target_item->full_price_bonus_manager($order_item->count, $order_item->weight, $discount);
                    $order_item->save(false);

//                    $old_type_handling = $order_item->getOrdersItemsHandings()->indexBy('type_handling_id')->all();
//                    if (isset($value['type_handling'])) {
//                        foreach ($value['type_handling'] as $type_handling) {
//                            if (!isset($old_type_handling[$type_handling])) {
//                                $insert_handing[$order_item->item_id][] = $type_handling;
//                            } else {
//                                unset($old_type_handling[$type_handling]);
//                            }
//                        }
//                    }
//                    if ($old_type_handling) {
//                        $delete_handling = [];
//                        foreach ($old_type_handling as $type_handling) {
//                            $delete_handling[] = $type_handling->id;
//                        }
//                        OrdersItemsHanding::deleteAll(['id' => $delete_handling]);
//                    }
                    unset($old_items[$key]);
                } else {
                    $target_item = $db_items[$key];
                    if (isset($value['price'])) {
                        if ($target_item->discount) {
                            $target_item->discount = 0;
                        }
                        $target_item->price = (int)$value['price'];
                    } elseif ($this->isWholesale) {
                        if ($target_item->discount) {
                            $target_item->discount = 0;
                        }
                        $target_item->price = $target_item->wholesale_price;
                    }
                    $weight = (isset($value['weight']) ? $value['weight'] : 0);
//                    $bonus = $target_item->price_bonus_manager();
                    $insert[] = [
                        'order_id' => (($this->isNewRecord) ? '' : $this->id),
                        'item_id' => $target_item->id,
                        'count' => $value['count'],
                        'price' => $target_item->price,
                        'weight' => $weight,
                        'purch_price' => 0,
                        'bonus_manager' => 0,
                        'data' => Json::encode($this->convert_to_array($db_items[$key])),
                    ];
                    $full_item_price = $functions->full_item_price($discount, $target_item, $value['count'], $weight);
                    $sum += $full_item_price;
//                    $full_bonus_manager += $target_item->full_price_bonus_manager($value['count'], $weight, $discount);
                    $full_purch_price += $target_item->sum_price($value['count']);
                    if (isset($value['type_handling'])) {
                        foreach ($value['type_handling'] as $type_handling) {
                            if (!isset($old_type_handling[$type_handling])) {
                                $insert_handing[$target_item->id][] = $type_handling;
                            } else {
                                unset($old_type_handling[$type_handling]);
                            }
                        }
                    }
                }
            }
        }
        $this->full_purch_price = $full_purch_price;
        $this->full_price = $sum;
        $sum = $sum - $this->discount($sum);
        if ($this->user_id) {
            $percent_bonus = Yii::$app->function_system->percent($this->user_id);
            if ($percent_bonus) {
                $full_bonus = floor(((int)$sum * ($percent_bonus)) / 100);
                $this->bonus_add = $full_bonus;
            }
        }
        $this->bonus_manager = $full_bonus_manager - $this->discount($full_bonus_manager);
        if ($old_items) {
            $deleted = [];
            foreach ($old_items as $old_item) {
                $deleted[] = $old_item->id;
            }
            if ($deleted) {
                Yii::$app->db->createCommand()->delete('orders_items', ['id' => $deleted])->execute();
            }
        }
        if ($insert) {
            if ($this->isNewRecord) {
                $this->data_insert['items'] = $insert;
            } else {
                Yii::$app->db->createCommand()->batchInsert(
                    'orders_items', [
                    'order_id',
                    'item_id',
                    'count',
                    'price',
                    'weight',
                    'purch_price',
                    'bonus_manager',
                    'data',
                ], $insert
                )->execute();
            }
        }
        if ($insert_handing) {
            if ($this->isNewRecord) {
                $this->data_insert['insert_handing'] = $insert_handing;
            } else {
                $insert = [];
                $old_items = $this->getOrdersItems()->indexBy('item_id')->all();
                foreach ($insert_handing as $key => $value) {
                    if (isset($old_items[$key])) {
                        foreach ($value as $val) {
                            $insert[] = [
                                'orders_items_id' => $old_items[$key]->id,
                                'type_handling_id' => $val,
                            ];
                        }
                    }
                }
                if ($insert) {
                    Yii::$app->db->createCommand()->batchInsert(
                        'orders_items_handing', [
                        'orders_items_id',
                        'type_handling_id',
                    ], $insert
                    )->execute();
                }
            }
        }
        ///Сеты
//        if ($old_sets) {
//            $deleted = [];
//            foreach ($old_sets as $old_set) {
//                $deleted[] = $old_set->id;
//            }
//            if ($deleted) {
//                Yii::$app->db->createCommand()->delete('orders_sets', ['id' => $deleted])->execute();
//            }
//        }
//        if ($insert_sets) {
//            if ($this->isNewRecord) {
//                $this->data_insert['sets'] = $insert_sets;
//            } else {
//                Yii::$app->db->createCommand()->batchInsert('orders_sets', [
//                    'order_id',
//                    'set_id',
//                    'count',
//                    'price',
//                    'purch_price',
//                    'bonus_manager'
//                ], $insert_sets)->execute();
//            }
//        }
    }

    public function saveOrderItems()
    {
        // Если новая запись заказа
        if ($this->data_insert) {
//            d::pe('if1');
            if (isset($this->data_insert['sets']) && ($insert = $this->data_insert['sets'])) {
                foreach ($insert as &$value) {
                    $value['order_id'] = $this->id;
                }
                Yii::$app->db->createCommand()->batchInsert(
                    'orders_sets', [
                    'order_id',
                    'set_id',
                    'count',
                    'price',
                    'purch_price',
                    'bonus_manager',
                ], $insert
                )->execute();
            }
            if (isset($this->data_insert['items']) && ($insert = $this->data_insert['items'])) {
                foreach ($insert as &$value) {
                    $value['order_id'] = $this->id;
                }
                Yii::$app->db->createCommand()->batchInsert(
                    'orders_items', [
                    'order_id',
                    'item_id',
                    'count',
                    'price',
                    'weight',
                    'purch_price',
                    'bonus_manager',
                    'data',
                ], $insert
                )->execute();
            }
            if (isset($this->data_insert['insert_handing']) && ($insert_handing = $this->data_insert['insert_handing'])) {
                $insert = [];
                $old_items = $this->getOrdersItems()->indexBy('item_id')->all();
                foreach ($insert_handing as $key => $value) {
                    if (isset($old_items[$key])) {
                        foreach ($value as $val) {
                            $insert[] = [
                                'orders_items_id' => $old_items[$key]->id,
                                'type_handling_id' => $val,
                            ];
                        }
                    }
                }
                if ($insert) {
                    Yii::$app->db->createCommand()->batchInsert(
                        'orders_items_handing', [
                        'orders_items_id',
                        'type_handling_id',
                    ], $insert
                    )->execute();
                }
            }
        }
    }

    public function lock($is_manager, $is_collector, $is_driver)
    {
        $result = [];
        if (Yii::$app->request->post('lock')) {
            if ($is_manager) {
                if (!$this->manager_id) {
                    $this->manager_id = Yii::$app->user->id;
                    $this->update_status = 15;
                } else {
                    $result['message']['error'] = 'Данный заказ уже принят другим менеджером';
                }
            } elseif ($is_collector) {
                if (!$this->collector_id) {
                    $this->collector_id = Yii::$app->user->id;
                    $this->update_status = 15;
                } else {
                    $result['message']['error'] = 'Данный заказ уже принят другим сборщиком';
                }
            } elseif ($is_driver) {
                if (!$this->driver_id) {
                    $this->driver_id = Yii::$app->user->id;
                    $this->status = 4;
                    $this->update_status = 15;
                    /**
                     * @var $user SUser
                     */
                    $this->changeHistory(5);
                } else {
                    $result['message']['error'] = 'Данный заказ уже принят другим водителем';
                }
            }
        }

        return $result;
    }

    public function unlock($is_manager, $is_collector, $is_driver)
    {
        $result = [];
        if (Yii::$app->request->post('unlock') && $this->status < 4) {
            $update = true;
            $update_data = [];
            if ($is_manager) {
                if ($this->manager_id == Yii::$app->user->id) {
                    $update_data = [
                        'manager_id' => null,
                    ];
                } else {
                    $update = false;
                }
            } elseif ($is_collector) {
                if ($this->collector_id == Yii::$app->user->id) {
                    $update_data = [
                        'collector_id' => null,
                    ];
                } else {
                    $update = false;
                }
            } elseif ($is_driver) {
                if ($this->driver_id == Yii::$app->user->id) {
                    $update_data = [
                        'status' => 3,
                        'driver_id' => null,
                    ];
                } else {
                    $update = false;
                }
            }
            if ($update) {
                $this->changeHistory(11);
                Orders::updateAll($update_data, ['id' => $this->id]);
                $result['url'] = Url::to(['orders/index']);
            } else {
                $result['message']['error'] = 'Данный заказ у другого пользователя';
            }
        }

        return $result;
    }

    public $data_status = [
        0 => 'Новый заказ',
        1 => 'Заказ на формировании',
        2 => 'Заказ на подтвержение клиентом',
        3 => 'Заказ подтверждён клиентом',
        4 => 'Доставка',
        5 => 'Заказ оплачен/выполнен',
        6 => 'Возврат',
        7 => 'Частичный возврат',
        8 => 'Отказ клиента',
        9 => 'Клиент не отвечает',
        10 => 'Возврат/Отказ клиента',
    ];

    public $data_pay_status = [
        'wait' => 'Не оплачен',
        'send_pay' => 'Ожидается ответ от платёжной системы',
        'success' => 'Платёж произведён',
        'wait_surcharge' => 'Ожидается доплата',
        'success_surcharge' => 'Доплата совершена',
        'success_rollback' => 'Подтверждён возврат (полный/частичный)',
    ];

//    public $data_status = [
//        0 => 'Новый заказ',
//        1 => 'Заказ на сборке',
//        2 => 'Подтверждение заказа клиентом',
//        3 => 'Доставка',
//        4 => 'Заказ оплачен/выполнен',
//        5 => 'Выгружен в 1С',
//        6 => 'Возврат',
//        7 => 'Частичный возврат',
//        8 => 'Отказ клиента',
//        9 => 'Клиент не отвечает',
//    ];
    protected $update_money = false;

    protected $rollback_collector = 0;

    /**
     * Выполенение заказа менеджером, сборщиком или водителем
     * @param $is_manager
     * @param $is_collector
     * @param $is_driver
     * @return array
     */
    public function success($is_manager, $is_collector, $is_driver)
    {
        $result = [];
        $success = Yii::$app->request->post('success');
        /*
         * Кнопки "На сборку" и "Подтвердить"
         * запускают этот IF
         */
        if ($success) {
//            d::pe('if($success)');
            $update = true;
            if ($is_manager) {
//                d::pe('Orders->success manager');
                if ($this->manager_id == Yii::$app->user->id) {
//                    d::pe('$success == '.$success);
//                    d::pe('$this->status == '.$this->status);
                    // Кнопка "На сборку"
                    if ($success == 1) {
                        if ($this->status == 0) {
                            $this->status = 1;
                            $this->update_status = 2;
                            /**@var $collectors SUser[] */
                            $collectors = SUser::findAll(['role' => 'collector']);
                            if ($collectors) {
                                $emails_collectors = [];
                                foreach ($collectors as $collector) {
                                    $emails_collectors[] = $collector->email;
                                }
                                if ($emails_collectors) {
                                    $this->send_message_status = [
                                        'user' => Yii::$app->user->identity,
                                        'emails' => $emails_collectors,
                                    ];
                                }
                            }
                        } elseif ($this->status == 4 || $this->status == 3) {
                            $this->commitBonus();
                            $this->date_delivery = time();
                            $this->status = 5;
                            $this->update_status = 6;
                        }
                    } elseif ($success == 2) {
                    // Кнопка "Подтвердить"
                        if ($this->status == 0 || $this->status == 2) {
                            $this->status = 3;
                            $this->update_status = 3;
                            if ($this->collector_id) {
                                /**@var $collector SUser */
                                $collector = SUser::findOne($this->collector_id);
                                $this->send_message_status = [
                                    'user' => Yii::$app->user->identity,
                                    'emails' => [
                                        $collector->email,
                                    ],
                                ];
                            } else {
                                /**@var $collectors SUser[] */
                                $collectors = SUser::findAll(['role' => 'collector']);
                                if ($collectors) {
                                    $emails_collectors = [];
                                    foreach ($collectors as $collector) {
                                        $emails_collectors[] = $collector->email;
                                    }
                                    if ($emails_collectors) {
                                        $this->send_message_status = [
                                            'user' => Yii::$app->user->identity,
                                            'emails' => $emails_collectors,
                                        ];
                                    }
                                }
                            }
                        }
                    } elseif ($success == 'status_1') {
                        if ($this->status == 1) {
                            $this->status = 2;
                            $this->update_status = 4;
                        }
                        if ($this->manager_id) {
                            /**@var $collector SUser */
                            $collector = SUser::findOne($this->manager_id);
                            $this->send_message_status = [
                                'user' => Yii::$app->user->identity,
                                'emails' => [
                                    $collector->email,
                                ],
                            ];
                        }
                    } elseif ($success == 'status_2') {
                        if ($this->status == 1 || $this->status == 2) {
                            $this->status = 3;
                            $this->update_status = 3;
                        }
                    } elseif ($success == 'status_3') {
                        if ($this->status < 5) {
                            if (false && !$this->driver_id) {
                                $result['message']['error'] = 'Выберите водителя!';
                                $result['errors'][Html::getInputId($this, 'driver_id')][] = 'Выберите водителя!';
                                $result['js'] = <<<JS
$('a[href="#page-responsible-panel"]').tab('show');
JS;

                                return $result;
                            } else {
                                $this->status = 4;
                                $this->update_status = 5;
                            }
                        }
                    }
                } else {
//                    d::pe('$update->false');
                    $update = false;
                }
            } elseif ($is_collector) {

//                d::pe('Orders->success collector');
                if ($this->collector_id == Yii::$app->user->id) {
                    if ($success == 1) {
                        if ($this->status == 1) {
                            $this->status = 2;
                            $this->update_status = 4;
                        }
                        if ($this->manager_id) {
                            /**@var $collector SUser */
                            $collector = SUser::findOne($this->manager_id);
                            $this->send_message_status = [
                                'user' => Yii::$app->user->identity,
                                'emails' => [
                                    $collector->email,
                                ],
                            ];
                        }
                    } elseif ($success == 2) {
                        if ($this->status == 1 || $this->status == 2) {
                            $this->status = 3;
                            $this->update_status = 3;
                        }
                        if ($this->manager_id) {
                            /**@var $collector SUser */
                            $manager = SUser::findOne($this->manager_id);
                            $this->send_message_status = [
                                'user' => Yii::$app->user->identity,
                                'emails' => [
                                    $manager->email,
                                ],
                            ];
                        }
                    } elseif ($success == 3) {
                        if ($this->status < 5) {
                            if (false && !$this->driver_id) {
                                $result['message']['error'] = 'Выберите водителя!';
                                $result['errors'][Html::getInputId($this, 'driver_id')][] = 'Выберите водителя!';
                                $result['js'] = <<<JS
$('a[href="#page-responsible-panel"]').tab('show');
JS;

                                return $result;
                            } else {
                                $this->status = 4;
                                $this->update_status = 5;
                                if ($this->manager_id) {
                                    /**@var $collector SUser* */
                                    $manager = SUser::findOne($this->manager_id);
                                    $this->send_message_status = [
                                        'user' => Yii::$app->user->identity,
                                        'emails' => [
                                            $manager->email,
                                        ],
                                    ];
                                }
                            }
                        }
                    }
                } else {
                    $update = false;
                }
            } elseif ($is_driver) {

//                d::pe('Orders->success driver');
                if ($this->driver_id == Yii::$app->user->id) {
                    if ($this->status < 5) {
                        $this->commitBonus();
                        $this->date_delivery = time();
                        $this->status = 5;
                        $this->update_status = 6;
                        /**@var $collector SUser */
                        $manager = SUser::findOne($this->manager_id);
                        $this->send_message_status = [
                            'user' => Yii::$app->user->identity,
                            'emails' => [
                                $manager->email,
                            ],
                        ];
                    }
                } else {
                    $update = false;
                }
            }
            if (!$update) {
                $result['message']['error'] = 'Данный заказ у другого пользователя';
            } else {
                $result['back'] = true;
            }
        }
//        d::pe('no if');
        if (Yii::$app->request->post('rollback_items')) {
//            d::pe('rollback_items');
            if (
                ($is_driver && $this->driver_id == Yii::$app->user->id)
                ||
                ($is_manager && $this->manager_id == Yii::$app->user->id)
            ) {
                /*
                 * Карасная кнопка "Возврат" запускает этот IF
                 */
                $result['url'] = Url::to(['orders/rollback-items', 'id' => $this->id]);
            }
        }
//        d::pe('success return');
        return $result;
    }

    protected $update_status = false;

    public function setUpdate_status($val)
    {
        $this->update_status = $val;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!$this->isNewRecord && $this->getAttribute('bonus_use') != $this->getOldAttribute('bonus_use')) {
            $log_data = [
                'id' => $this->id,
                'user_change_id' => Yii::$app->user->id,
                'old_attributes' => Json::encode($this->getOldAttributes()),
                'new_attributes' => Json::encode($this->getAttributes()),
            ];
            Yii::$app->db->createCommand()->insert(
                's_log_action', [
                    'action' => 'change_use_bonus',
                    'data' => Json::encode($log_data),
                    'time' => time(),
                ]
            )->execute();
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    protected $send_message_status = false;

    /**
     * This method is called at the end of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is true,
     * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is false. The event class used is [[AfterSaveEvent]].
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param boolean $insert whether this method called while inserting a record.
     *                                   If false, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     *                                   You can use this parameter to take action based on the changes made for example send an email
     *                                   when the password had changed or implement audit trail that tracks all the changes.
     *                                   `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     *                                   already the new, updated values.
     */
    public function afterSave($insert, $changedAttributes)
    {
        /**
         * @var $user SUser
         */
        $action = false;
        if ($insert) {
            $action = 1;
        } else {
            if ($this->update_status !== false) {
                $action = $this->update_status;
            }
        }
        if ($action !== false) {
            $this->changeHistory($action);
            //Если заказ выполнен то отправляем снятие денег
            if ($action == 6) {
                $this->successPay();
            }
        }
        if ($this->send_message_status !== false && isset($this->send_message_status['emails']) && $this->send_message_status['emails']) {
            /**@var $mailer \yii\swiftmailer\Message* */
            $send_mails = $this->send_message_status['emails'];
            $data = $this->send_message_status;
            $data['item'] = $this;
            $mailer = \Yii::$app->mailer->compose(['html' => 'admin/info_order'], $data)
                ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->params['siteName'] . ' info'])
                ->setTo($send_mails)
                ->setSubject('Сообщение с сайта ' . \Yii::$app->params['siteName'] . '.kz');
            $mailer->send();
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function changeHistory($action)
    {
        /**
         * @var $user SUser
         */
        $user = Yii::$app->user->identity;
        $name = $user->username;
        switch ($user->role) {
            case 'manager':
                $name .= ' (Менеджер)';
                break;
            case 'collector':
                $name .= ' (Сборщик)';
                break;
            case 'driver':
                $name .= ' (Водитель)';
                break;
            case 'kassir':
                $name .= ' (Кассир)';
                break;
            case 'admin':
                $name .= ' (Администратор)';
                break;
        }
        $history = new OrdersHistory();
        $history->user_id = $user->id;
        $history->user_name = $name;
        $history->order_id = $this->id;
        $history->action = $action;
        $history->save(false);
    }

    public function commitBonus()
    {
        if ($this->user_id && $this->enable_bonus) {
            $bonus = $this->bonus_add;
            $time = time();
            $history = new HistoryBonus();
            $history->user_id = $this->user_id;
            $history->created_at = $time;
            $history->name = 'Заказ №' . str_pad($this->id, 9, '0', STR_PAD_LEFT);
            $history->sum = $bonus;
            $history->save(false);
            User::updateAllCounters(['bonus' => $bonus], ['id' => $this->user_id]);
            User::updateAllCounters(
                ['order_sum' => ($this->full_price - $this->discount($this->full_price))], ['id' => $this->user_id]
            );
            $referralSystem = new ReferralSystem();
            $referralSystem->successInvited($this->id);
            $referralSystem->addBonus($this->full_price - $this->discount($this->full_price), $this->user_id);
        }
        if ($this->manager_id) {
            $history = new SHistoryMoney();
            $history->user_id = $this->manager_id;
            $history->sum_order = $this->full_price - $this->discount($this->full_price);
            $history->sum_purch = $this->full_purch_price;
            $history->sum_bonus = $this->bonus_manager;
            $history->date_created = time();
            $history->save(false);
            /**
             * @var $user \common\models\User
             */
            if ($this->user_id && (
                $user = User::findOne(['id' => $this->user_id])
                )
            ) {
                if (!$user->manager_id) {
                    if (Orders::find()->where(['user_id' => $this->user_id, 'manager_id' => $this->manager_id])->count() > 2) {
                        Yii::$app->db->createCommand()->update(
                            $user->tableName(),
                            ['manager_id' => $this->manager_id],
                            ['id' => $user->id]
                        )->execute();
                    }
                }
            }
        }
        if ($this->driver_id) {
            /**@var $driver SUser */
            $driver = SUser::findOne($this->driver_id);
            if ($driver) {
//                $this->bonus_driver = $driver->bonus_delivery;
            }
        }
    }

    public function rollbackBonus()
    {
        if ($this->user_id && $this->bonus_use) {
            User::updateAllCounters(['bonus' => $this->bonus_use], ['id' => $this->user_id]);
        }
    }

    /**
     * @param $item Items
     * @return mixed
     */
    public function convert_to_array($item)
    {
        $item_data = $item->getAttributes();
//        if ($item->itemsTogethers) {
//            foreach ($item->itemsTogethers as $itemsTogether) {
//                $item_data['itemsTogethers'][$itemsTogether->id] = $itemsTogether->getAttributes();
//                $item_data['itemsTogethers'][$itemsTogether->id]['item'] = $itemsTogether->item->getAttributes();
//            }
//        }
        return $item_data;
    }

    /**
     * @param $order_item OrdersItems
     * @param $item       Items
     * @return mixed
     */
    public function convert_to_model($order_item, $item)
    {
        $data = Json::decode($order_item->data);
        $item->setAttributes($data, false);
//        if (isset($data['itemsTogethers'])) {
//            $itemsTogethers = [];
//            foreach ($data['itemsTogethers'] as $itemsTogether) {
//                $model = new ItemsTogether();
//                $model->setAttributes($itemsTogether, false);
//                $model_item = new Items();
//                $model_item->setAttributes($itemsTogether['item']);
//                $model->populateRelation('item', $model_item);
//                $itemsTogethers[] = $model;
//            }
//            if ($itemsTogethers) {
//                $item->populateRelation('itemsTogethers', $itemsTogethers);
//            }
//        }
        return $item;
    }

    public function convert_all_to_model($old_order_items, $db_items)
    {
        if (isset($old_order_items[0])) {
            $old_order_items = ArrayHelper::map(
                $old_order_items,
                function ($el) {
                    return $el->item_id;
                },
                function ($el) {
                    return $el;
                }
            );
        }
        foreach ($db_items as &$db_item) {
            if (isset($old_order_items[$db_item->id])) {
                $db_item = $this->convert_to_model($old_order_items[$db_item->id], $db_item);
            }
        }

        return $db_items;
    }

    public function to_session_items($items)
    {
        $result = [];
        foreach ($items as $key => $value) {
            $result[$key] = $value['count'];
        }

        return $result;
    }

    public function discount($price)
    {
        $discount = preg_replace("#([^-\d%]*)#u", '', $this->discount);
        if ($discount) {
            if (preg_match("#\%$#u", $discount)) {
                $discount = preg_replace("#\%$#u", '', $discount);
                $price = round(((double)$price * (double)$discount) / 100);
            } else {
                $price = $discount;
            }
        } else {
            $price = 0;
        }

        return $price;
    }

    public function realSum()
    {
        return (($this->full_price + $this->price_delivery) - $this->discount($this->full_price)) - $this->bonus_use;
    }

    public function addSumPay()
    {
        $sum_pay = OrdersPay::find()->where(['order_id' => $this->id, 'status' => [0, 1]])->sum('amount');

        return $this->realSum() - floatval($sum_pay);
    }

    public function sendAddPay($sum)
    {
        $request = [
            'Amount' => $sum,
            'Currency' => 'KZT',
            'Description' => 'Оплата заказ на сайте mymix.kz',
            'InvoiceId' => $this->id,
            'RequireConfirmation' => 'true',
        ];
        if ($this->user_id) {
            $request['AccountId'] = $this->user_id;
        }
        if ($this->user_mail) {
            $request['Email'] = $this->user_mail;
            $request['SendEmail'] = 'true';
        }
        if ($this->user_phone) {
            $request['Phone'] = preg_replace('/[^0-9]*/', '', $this->user_phone);
            $request['SendSms'] = 'true';
        }
        $result_request = Orders::sendRequest('/orders/create', $request);
        if ($result_request['Success'] == true) {
            Orders::updateAll(['pay_status' => 'wait_surcharge'], ['id' => $this->id]);

            return $result_request['Model']['Url'];
        } else {
            Yii::error(var_export($result_request, true), 'application.payment');
            Yii::error(var_export($request, true), 'application.payment');

            return false;
        }
    }

    public function successPay()
    {
        $sum = $this->realSum();
        /** @var OrdersPay[] $payments */
        $payments = OrdersPay::find()->where(['order_id' => $this->id, 'status' => [0, 1]])->orderBy(
            ['status' => SORT_DESC]
        )->all();
        foreach ($payments as $payment) {
            if (!$sum && $payment->status == 0) {
                Orders::sendRollbackPay($payment->id);
                OrdersPay::updateAll(['status' => 3], ['id' => $payment->id]);
            } else {
                if ($payment->status == 0) {
                    //Если сумма больше чем платёж то подтверждаем всю сумму, а если меньше то остаток он sum
                    if ($sum >= floatval($payment->amount)) {
                        Orders::sendSuccessPay($payment->id, $payment->amount);
                        OrdersPay::updateAll(
                            ['status' => 1, 'real_amount' => $payment->amount], ['id' => $payment->id]
                        );
                    } else {
                        Orders::sendSuccessPay($payment->id, floatval($payment->amount) - $sum);
                        OrdersPay::updateAll(
                            ['status' => 1, 'real_amount' => floatval($payment->amount) - $sum], ['id' => $payment->id]
                        );
                    }
                }
                $sum = $sum - floatval($payment->amount);
            }
        }
    }

    public function rollbackAllPay()
    {
        /** @var OrdersPay[] $payments */
        $payments = OrdersPay::find()->where(['order_id' => $this->id, 'status' => [0, 1]])->all();
        foreach ($payments as $payment) {
            Orders::sendRollbackPay($payment->id);
            OrdersPay::updateAll(['status' => 3], ['id' => $payment->id]);
        };
    }

    public static function sendSuccessPay($TransactionId, $sum)
    {
        $request = [
            'TransactionId' => $TransactionId,
            'Amount' => floatval($sum),
        ];
        Orders::sendRequest('/payments/confirm ', $request);
    }

    public static function sendRollbackPay($TransactionId)
    {
        $request = [
            'TransactionId' => $TransactionId,
        ];
        Orders::sendRequest('/payments/void', $request);
    }

    public static function sendRequest($endpoint, array $params = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.cloudpayments.ru' . $endpoint);
        curl_setopt(
            $curl, CURLOPT_USERPWD, sprintf(
                '%s:%s', Yii::$app->params['cloudpayments']['publicId'], Yii::$app->params['cloudpayments']['apiKey']
            )
        );
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($curl);
        curl_close($curl);

        return (array)json_decode($result, true);
    }
}
