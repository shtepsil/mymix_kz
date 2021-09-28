<?php

namespace frontend\form;

use common\components\Debugger as d;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Orders;
use backend\modules\catalog\models\OrdersHistory;
use backend\modules\catalog\models\OrdersItems;
use backend\modules\catalog\models\OurStores;
use backend\modules\catalog\models\Sales;
use common\components\ReferralSystem;
use common\models\Delivery;
use common\models\PromoCode;
use common\models\User;
use common\models\UserAddress;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class Order
 * @package frontend\form
 * @property array $data_payment
 */
class Order extends Model
{
    //region Информация о покупателе
    public $first_name;
    public $last_name;
    public $phone;
    public $email;
    public $isEntity = 0;
    //endregion
    //region Адрес доставки
    public $city;
    public $street;
    public $home;
    public $house;
    //endregion
    //region Информация о заказе
    public $address_id;
    public $payment;
    public $time_order;
    public $bonus = 0;
    public $code;
    public $comments;
    //endregion
    public $type_delivery = 1;//способ получения 1= доставка, 0= самовывоз
    public $delivery;
    public $only_pickup = 0;
    public $our_stories_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //region Информация о покупателе
            [['first_name', 'last_name', 'email', 'phone'], 'trim', 'on' => ['isGuest']],
            [['first_name', 'last_name'], 'required', 'on' => ['isGuest']],
            [['first_name', 'last_name'], 'string', 'max' => 255],
            [['phone'], 'trim'],
            [['phone'], 'required'],
            ['email', 'email', 'on' => ['isGuest']],
//            ['email', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'email', 'on' => ['isGuest']],
            ['isEntity', 'boolean', 'on' => ['isGuest']],
            //endregion
            //region Адрес доставки
            [['street', 'home'], 'trim', 'on' => ['isGuest', 'no_address']],
            [['city', 'street', 'home'], 'required', 'on' => ['isGuest', 'no_address'], 'isEmpty' => [$this, 'no_delivery_required']],
            [['city'], 'integer'],
            [['street', 'home', 'house', 'delivery'], 'string', 'max' => 255],
            [['our_stories_id'], 'integer'],
            //endregion
            //region Информация о заказе
            [['phone'], 'match', 'pattern' => '/^((\+?7)(\(?\d{3})\)-?)?(\d{3})(-?\d{4})$/', 'message' => \Yii::t('main', 'Некорректный формат поля {attribute}')],
            ['address_id', 'required', 'on' => 'is_address', 'isEmpty' => [$this, 'no_delivery_required']],
            [['payment'], 'required'],
            [['time_order'], 'required', 'isEmpty' => [$this, 'no_delivery_required']],
            [['bonus'], 'boolean'],
            [['code', 'comments'], 'string', 'max' => 255],
            [['time_order', 'bonus', 'code'], 'safe'],
            //endregion
            //region Безопасные аттрибуты, без этого форма не будет принимать
            [['city', 'street', 'home', 'house', 'first_name', 'last_name', 'email', 'phone', 'address_id', 'payment', 'isEntity', 'type_delivery', 'only_pickup'], 'safe'],
            //endregion
        ];
    }

    public function no_delivery_required($value)
    {
        if ($this->delivery == 'delivery_method_pickup') {
            return false;
        } else {
            return $value === null || $value === [] || $value === '';
        }
    }

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
        return 'order';
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
        /**
         * @var $user \common\models\User
         */
//        if($this->isEntity==1){
//            $this->scenario = 'entity';
//        }
        if (\Yii::$app->user->isGuest) {
            $this->scenario = 'isGuest';
        } else {
            if (!$this->address_id || $this->address_id == 'none') {
                $this->scenario = 'no_address';
            } else {
                $this->scenario = 'is_address';
            }
        }
        return parent::beforeValidate(); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            //region Информация о покупателе
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'phone' => 'Телефон',
            'email' => 'E-Mail',
            'isEntity' => 'Юридическое лицо',
            'entity_name' => 'Юр. название',
            'entity_address' => 'Юр. адрес',
            'entity_bin' => 'БИН',
            'entity_iik' => 'ИИК',
            'entity_bank' => 'Банк',
            'entity_bik' => 'БИК',
            'entity_nds' => 'Плательщик НДС',
            //endregion
            //region Адрес доставки
            'city' => 'Город',
//            'index' => 'Почтовый индекс',
            'street' => 'Улица',
            'home' => 'Дом',
            'house' => 'Квартира',
            //endregion
            //region Информация о заказе
            'time_order' => 'Удобное время (указано местное время)',
            'address_id' => 'Адрес доставки',
            'bonus' => 'Бонус',
            'payment' => 'Способ оплаты',
            'comments' => 'Примечание к заказу',
            'code' => 'Код',
            //endregion
            'delivery' => 'Способ доставки',
            'our_stories_id' => 'Пункт самовывоза',
        ];
    }

    public $data_city = [
        1 => 'Алматы',
        2 => 'Астана'
    ];
    public $time_days = [
        //12 => '09:00-12:00',
        //15 => '12:00-15:00',
        //18 => '15:00-18:00',
        //21 => '18:00-21:00',
        9 => '09:00-13:00',
        13 => '13:00-18:00',
    ];

    public function getData_payment()
    {
        return [
            1 => \Yii::$app->settings->get('payment_type_cash'),
            2 => \Yii::$app->settings->get('payment_type_online'),
            3 => \Yii::$app->settings->get('payment_type_cards'),
        ];
    }

    public function send()
    {

        /**
         * $this - объект Order с загруженными пользовательскими данными.
         * Данные загружаются тут:
         * frontend\components\SendFormAction.php
         * $form->load(Yii::$app->request->post());
         */

        /**
         * @var $user      \common\models\User
         * @var $functions \frontend\components\FunctionComponent
         */
        $functions = Yii::$app->function_system;
        $result = [];
        $data = [];
        $connect = \Yii::$app->db;
        $transaction = $connect->beginTransaction();

        /*
         * =====================================
         * Сохранение пользовательских данных
         * $user->save()
         * =====================================
         */
        if (\Yii::$app->user->isGuest) {
            if ($this->email && !User::findByUsername($this->email)) {

                $user = new User();
                if ($this->isEntity == 1) {
                    $entity = [
                        'entity_name' => '',
                        'entity_address' => '',
                        'entity_bin' => '',
                        'entity_iik' => '',
                        'entity_bank' => '',
                        'entity_bik' => '',
                        'entity_nds' => 0,
                    ];
                    $user->data = Json::encode($entity);
                }
                $user->isEntity = $this->isEntity;
                $user->email = $this->email;
                $user->city_id = $this->city;
                $user->phone = $this->phone;
                $user->username = $this->first_name . ' ' . $this->last_name;
                $user->status = $user::STATUS_ACTIVE;
                $user->password = \Yii::$app->security->generateRandomString(6);
                $user->generateAuthKey();
                if (!$user->save()) {
                    $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
                    return $result;
                }
            } elseif ($this->phone) {
                $attributes_user = [];
                $attributes_user['isEntity'] = $this->isEntity;
                $attributes_user['username'] = $this->first_name . ' ' . $this->last_name;
                //$attributes_user['email'] = $this->email;
                $attributes_user['city_id'] = $this->city;
                if ($this->isEntity == 1) {
                    $entity = [
                        'entity_name' => '',
                        'entity_address' => '',
                        'entity_bin' => '',
                        'entity_iik' => '',
                        'entity_bank' => '',
                        'entity_bik' => '',
                        'entity_nds' => 0,
                    ];
                    $attributes_user['data'] = Json::encode($entity);
                }
                $user = User::checkPhone($this->phone, $attributes_user);
            } else {
                $user = null;
            }
        } else {
            $user = \Yii::$app->user->identity;
            $update_user = false;
            if (!$user->phone) {
                $user->phone = $this->phone;
                $update_user = true;
            }
            if (!$user->city_id && $this->city) {
                $user->city_id = $this->city;
                $update_user = true;
            }
            if ($update_user) {
                $user->save(false);
            }
        }
        /*
         * ===================================================
         * //сохранение пользовательских данных $user->save()
         * END
         * ===================================================
         */

        $time = time();
        $enable_discount = true;
        $data = [
            'user_comments' => $this->comments,
            'user_phone' => $this->phone,
            'code' => $this->code,
            'bonus_use' => 0,
            'payment' => $this->payment,
            'status' => 0,
            'city_id' => $this->city,
            'created_at' => $time,
            'updated_at' => $time,
        ];
        if ($user) {
            if (doubleval($user->discount)) {
                $data['discount'] = $user->discount . '%';
                $enable_discount = false;
            }
            $data['isWholesale'] = $user->isWholesale;
            if ($user->isWholesale == 1) {
                $enable_discount = false;
            }
        } else {
            $data['isWholesale'] = 0;
        }
        $referralSystem = new ReferralSystem();
        $user_invite = null;
        /**
         * @var $code_model PromoCode
         */
        if (($code = \Yii::$app->request->post('code'))) {
            if ($user) {
                $user_invite = User::find()->where(['code' => $code])->one();
                if ($user_invite && $user_invite->id != $user->id && !$referralSystem->hasInvited($user->id)) {
                    $data['discount'] = '5%';
                } else {
                    $user_invite = null;
                }
            }
            if (($code_model = PromoCode::find()->andWhere(['code' => $code])->one()) && $code_model->check_enable()) {
                $enable_discount = false;
                $data['promo_code_id'] = $code_model->id;
                $data['discount'] = $code_model->discount;
            }
        } else {
            $code_model = false;
        }
        if (!$user) {
            $data['user_name'] = $this->first_name . ' ' . $this->last_name;
            $data['user_phone'] = $this->phone;
            $data['user_mail'] = $this->email;
            $data['isEntity'] = $this->isEntity;
            if ($this->type_delivery == 0) {
                $data['user_address'] = 'Самовывоз';
                $data['city_id'] = \Yii::$app->session->get('city_select', 1);
            } else {
                $data['user_address'] = 'г.' . $functions->data_city[$this->city] . ', ул. ' . $this->street . ', дом. ' . $this->home . (($this->house) ? (', кв. ' . $this->house) : '');
            }
        } else {
            $data['user_id'] = $user->id;
            $data['user_name'] = $user->username;
//            $data['user_phone'] = $user->phone;
            $data['user_mail'] = $user->email;
            $data['isEntity'] = $user->isEntity;
            if ($this->type_delivery == 0) {
                $data['user_address'] = 'Самовывоз';
                $data['city_id'] = \Yii::$app->session->get('city_select', 1);
            } else {
                if ($this->address_id != 'none' && $this->address_id) {
                    /**
                     * @var $address UserAddress
                     */
                    $address = UserAddress::find()->where(['id' => $this->address_id, 'user_id' => $user->id])->one($connect);
                    $data['city_id'] = $address->city;
                    $data['user_address'] = 'г.' . $address->data_city[$address->city] . ', ул. ' . $address->street . ', дом. ' . $address->home . (($address->house) ? (', кв. ' . $address->house) : '');
                } else {
                    $data['user_address'] = 'г.' . $functions->data_city[$this->city] . ', ул. ' . $this->street . ', дом. ' . $this->home . (($this->house) ? (', кв. ' . $this->house) : '');

                    $data_items_[] = [
                        'street' => $this->street,
                        'home' => $this->home,
                        'phone' => $this->phone,
                        'house' => (($this->house) ? ($this->house) : ''),
                        'city' =>$this->city,
                        'user_id' => $user->id,
                        'isMain' => 0
                    ];

                    $connect->createCommand()
                        ->batchInsert('user_address', ['street', 'home', 'phone', 'house', 'city', 'user_id', 'isMain'], $data_items_)
                        ->execute();
                }
            }
        }
        if ($this->only_pickup == 1) {
            $data['user_address'] = 'Самовывоз';
        }
        $data['date_delivery'] = $time;
        $data['time_delivery'] = $this->time_days[$this->time_order];
        /**
         * @var $items Items[]
         */
//        $sessions_items = Yii::$app->session->get('items', []);
        $sessions_items = Yii::$app->c_cookie->get('items', []);
        $sum = 0;
        $full_purch_price = 0;
        $data_items = $data_sets = $insert_handing = [];
        $saleData = (new Sales())->getSale($sessions_items);
//        $gifts = Yii::$app->session->get('gifts', []);
        $gifts = Yii::$app->c_cookie->get('gifts', []);

        $customerReceipt = [
            'Items'=>[],
            //место осуществления расчёта, по умолчанию берется значение из кассы
            'calculationPlace'=>Yii::$app->params['site'],
            //e-mail покупателя, если нужно отправить письмо с чеком
            'email'=>($user->email!='')?$user->email:'',
            //телефон покупателя в любом формате, если нужно отправить сообщение со ссылкой на чек
            'phone'=>($user->phone!='')?$user->phone:'',
            /*
             * // тег-1227 Покупатель - наименование организации или фамилия, имя, отчество (при наличии),
             * серия и номер паспорта покупателя (клиента)
             */
            'customerInfo' => ($user->username!='')?$user->username:'',
        ];

        $weight = 0;
        $order = new Orders($data);
        if ($sessions_items) {
            $q = new ActiveQuery(Items::className());
            $q->indexBy('id')
                ->andWhere(['id' => array_keys($sessions_items)]);
            $items = $q->all();
            if ($enable_discount) {
                $discount = $functions->discount_sale_items($items, $sessions_items);
            } else {
                $discount = [];
            }
            $bonus = 0;
            foreach ($items as $key => $item) {
                $count = $sessions_items[$key];
                $price = -1;
                $handling = [];
                if ($data['isWholesale'] == 1 && $item->wholesale_price) {
                    $clone_item = clone $item;
                    $price_item = $item->wholesale_price;
                    if ($clone_item->discount) {
                        $clone_item->discount = 0;
                    }
                    $clone_item->price = $clone_item->wholesale_price;
//                    $bonus = $clone_item->price_bonus_manager();
                } else {
//                    $bonus = $item->price_bonus_manager();
                    if (!empty($gifts[$key])) {
                        $sale = Sales::find()
                            ->select(['id', 'gifts'])
                            ->where(['active' => 1])
                            ->andWhere(['not', ['gifts' => null]])
                            ->andWhere(['id' => $gifts[$key]])
                            ->one();

                        if (!empty($sale)) {
                            foreach ($sale->gifts as $gift) {
                                if ($gift['id'] == $key) {
                                    $price = $gift['price'];
                                    break;
                                }
                            }
                        }
                    }

                    $price_item = $item->real_price();

                    if ($price > -1) {
                        $price_item = $price * $count;
                    }
                    elseif (!empty($saleData) && $saleData['value'] > 0) {
                        if ($saleData['type'] == 'percent') {
                            $price_item = $price_item - (($price_item*$saleData['value'])/100);
                        }
                        else {
                            $price_item -= $saleData['value'];
                        }
                    }
                }

                $weight += $item->weight * $count;

                $data_items[] = [
                    'order_id' => '{order_id}',
                    'item_id' => $item->id,
                    'count' => $count,
                    'price' => $price_item,
                    'purch_price' => 0,
                    'bonus_manager' => $bonus,
                    'data' => Json::encode($order->convert_to_array($item))
                ];

                if ($enable_discount) {
                    if ($price > -1) {
                        $sum += $price * $count;
                    }
                    else {
                        $sum += $functions->full_item_price($discount, $item, $count, 0, $saleData);
                    }
                } else {
                    if ($price > -1) {
                        $sum += $price * $count;
                    }
                    else {
                        $priceSum = $item->sum_price($count);

                        if (!empty($saleData) && $saleData['value'] > 0) {
                            if ($saleData['type'] == 'percent') {
                                $priceSum = $price_item - (($price_item*$saleData['value'])/100);
                            }
                            else {
                                $priceSum -= $saleData['value'];
                            }
                        }

                        $sum += $priceSum;
                    }
                }

//                $full_purch_price += $item->sum_price($count);
                if ($handling) {
                    foreach ($handling as $type_handling) {
                        $insert_handing[$item->id][] = $type_handling;
                    }
                }

                // Массив для CloudPayments
                $customerReceipt['Items'][] = [
                    'label'=>$item->name,
                    'price'=>$price_item,
                    'quantity'=>$count,
                    'amount'=>$price_item * $count,
                    'vat'=>'0',
                ];
            }//foreach

            $customerReceipt['amounts']=[
                // Сумма оплаты электронными деньгами
                'electronic'=>($sum),
            ];
			
        }

        $data['full_purch_price'] = $full_purch_price;

        $city = DeliveryPrice::find()->where(['id' => $this->city])->one();
        $delivery = (new Delivery())->getCost($sum, ($weight < 1 ? 1 : $weight), $city, $this->delivery);
        $data['price_delivery'] = (!empty($delivery) && $delivery['price'] > 0 ? $delivery['price'] : 0);
        $data['delivery'] = $this->delivery;
        $data['our_stories_id'] = $this->our_stories_id;

        $data['full_price'] = $sum;
        $percent_bonus = Yii::$app->function_system->percent();
        if ($discount_price = $order->discount($sum)) {
            $sum = $sum - $discount_price;
        }
        $full_bonus = floor(((int)$sum * ($percent_bonus)) / 100);
        $data['bonus_add'] = $full_bonus;
        if (!Yii::$app->user->isGuest && $this->bonus == 1) {
            $bonus_user = (int)Yii::$app->user->identity->bonus;
            $use_bonus = 0;
            $update_bonus = false;
            if ($bonus_user) {
                if ($bonus_user >= $sum) {
                    $update_bonus = $bonus_user - $sum;
                    $use_bonus = $sum;
                } elseif ($bonus_user < $sum) {
                    $update_bonus = 0;
                    $use_bonus = $bonus_user;
                }
                if (!is_bool($update_bonus)) {
                    User::updateAll(['bonus' => $update_bonus], ['id' => Yii::$app->user->id]);
                }
            }
            $data['bonus_use'] = $use_bonus;
        }
        if ($data['payment'] == 2) {
            $data['pay_status'] = 'wait';
        }
        if ($connect->createCommand()->insert('orders', $data)->execute() && ($data_items || $data_sets)) {
            \Yii::$app->session->remove('invited_code');
            $order_id = $connect->getLastInsertID();
            if ($user_invite && $user) {
                $referralSystem->addInvited($user_invite->id, $user->id, $order_id);
            }
            if (isset($data['bonus_use']) && $data['bonus_use']) {
                $log_data = $data;
                $log_data['id'] = $order_id;
                $connect->createCommand()->insert('s_log_action', [
                    'action' => 'user_use_bonus',
                    'data' => Json::encode($log_data),
                    'time' => $time,
                ])->execute();
            }
            $send = false;
            if ($data_items) {
                foreach ($data_items as &$order_item) {
                    $order_item['order_id'] = $order_id;
                }
                if ($connect->createCommand()
                    ->batchInsert('orders_items', ['order_id', 'item_id', 'count', 'price', 'purch_price', 'bonus_manager', 'data'], $data_items)
                    ->execute()
                ) {
                    $send = true;
                    if ($insert_handing) {
                        $insert = [];
                        $old_items = OrdersItems::find()->where(['order_id' => $order_id])->indexBy('item_id')->all();
                        foreach ($insert_handing as $key => $value) {
                            if (isset($old_items[$key])) {
                                foreach ($value as $val) {
                                    $insert[] = [
                                        'orders_items_id' => $old_items[$key]->id,
                                        'type_handling_id' => $val
                                    ];
                                }
                            }
                        }
                        if ($insert) {
                            Yii::$app->db->createCommand()->batchInsert('orders_items_handing', [
                                'orders_items_id',
                                'type_handling_id'
                            ], $insert)->execute();
                        }
                    }
                }
            }
            if ($data_sets) {
                foreach ($data_sets as &$order_set) {
                    $order_set['order_id'] = $order_id;
                }
                if ($connect->createCommand()
                    ->batchInsert('orders_sets', ['order_id', 'set_id', 'count', 'price', 'purch_price', 'bonus_manager'], $data_sets)
                    ->execute()
                ) {
                    $send = true;
                }
            }
            if ($send) {
                if ($code_model && $code_model->type == 'one') {
                    $code_model->isEnable = 0;
                    $code_model->save(false);
                }
                $json_customerReceipt = json_encode([]);
                if(count($customerReceipt)){
                    $json_customerReceipt = json_encode($customerReceipt);
                }
                $history = new OrdersHistory();
                $history->user_name = $data['user_name'];
                $history->order_id = $order_id;
                $history->action = 1;
                $history->save(false);
                $url = Url::to(['site/success-order']);
                $order_model = Orders::findOne($order_id);
                if ($order->payment == 2) {
                    \Yii::$app->session->set('success_order_pay', $order_id);
                    $sum_real = $order_model->realSum();
                    $CloudPaymentPublicId = Yii::$app->params['cloudpayments']['publicId'];
                    $result['js'] = <<<JS
	var widget = new cp.CloudPayments(),
	    receipt = {$json_customerReceipt},
	    res = $('.res');
		
	widget.auth({ // options
		publicId : '{$CloudPaymentPublicId}', //id из личного кабинета
		description : 'Оплата заказа на сайте mymix.kz', //назначение
		amount : {$sum_real}, //сумма
		currency : 'KZT', //валюта
		invoiceId : '{$order_id}', //номер заказа  (необязательно)
		accountId : '{$order_model->user_id}', //идентификатор плательщика (необязательно)
		email:'{$order_model->user_mail}',
		data:{ //содержимое элемента data
            "cloudPayments": {
              "CustomerReceipt": receipt //онлайн-чек
            }
        }
	},
		function (options) { // success
		window.location='{$url}'
	},
		function (reason, options) { // fail
		 $.growl.error({title: 'Ошибка', message: "Оплата не произошла", duration: 5000});
	});
JS;

					Yii::$app->c_cookie->remove('items');
                    Yii::$app->c_cookie->remove('gifts'); 

                } else {
                    $result['message']['success'] = 'Ваш заказ успешно отпрален!';
                    $result['js'] = <<<JS
window.location='{$url}'
JS;
                    /**
                     * @var $mailer \yii\swiftmailer\Message
                     */
                    $send_mails = explode(',', \Yii::$app->settings->get('admin_email', 'info@upw.kz'));
                    foreach ($send_mails as $key_email => &$value_email) {
                        if (!($value_email = trim($value_email, " \t\n\r\0\x0B"))) {
                            unset($send_mails[$key_email]);
                        }
                    }

                    $deliveryMethods = Delivery::getDeliveriesName();

                    if (!empty($order_model->our_stories_id)) {
                        $story = OurStores::findOne(['id' => $order_model->our_stories_id]);

                        if (!empty($story)) {
                            $name_pickup = $story->name;
                        }
                    }

					\Yii::$app->mailer->compose(['html' => 'admin/order'], ['order' => $order_model, 'delivery' => $deliveryMethods[$order_model->delivery], 'name_pickup' => (isset($name_pickup) ? $name_pickup : ''), 'days' => $delivery['days']])
					->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->params['siteName'] . ' info'])
					->setTo($send_mails)
					->setSubject('Новый заказ на сайте ' . \Yii::$app->params['siteName'])->send();
					if ($data['user_mail']) {
						\Yii::$app->mailer->compose(['html' => 'order'], ['order' => $order_model, 'delivery' => $deliveryMethods[$order_model->delivery], 'name_pickup' => (isset($name_pickup) ? $name_pickup : ''), 'days' => $delivery['days']])
							->setFrom([\Yii::$app->params['supportEmail'] => 'Интернет-магазин ' . \Yii::$app->params['siteName'] . '.kz'])
							->setTo($data['user_mail'])
							->setSubject('Заказ на сайте ' . \Yii::$app->params['siteName'] . '.kz')->send();
					}
                    
//                    Yii::$app->session->remove('items');
//                    Yii::$app->session->remove('gifts');
                    Yii::$app->c_cookie->remove('items');
                    Yii::$app->c_cookie->remove('gifts');
                }
                \Yii::$app->session->set('success_order', $order_id);
                $transaction->commit();
            } else {
                $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
                return $result;
            }
        } else {
            $transaction->rollBack();
        }
        return $result;
    }
}