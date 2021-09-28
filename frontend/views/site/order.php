<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $items Items[]
 * @var $a_address \common\models\UserAddress[]
 * @var $items_session array
 * @var $sets_session array
 * @var $invited_code string
 */

use common\components\Debugger as d;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Orders;
use common\models\BonusSettings;
use common\models\UserAddress;

use frontend\form\Order;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$context = $this->context;
$city = $context->city;
$no_address = true;
$user = $context->user;
$model = new Order();

$form_name = strtolower($model->formName());
$data_address = [];
$data_phones = [];
$a_address = [];

if ($user) {
    $a_address = UserAddress::find()->andWhere(['user_id' => $user->id])->orderBy(['isMain' => SORT_DESC])->all();
    if ($a_address) {
        $no_address = false;
        $start = true;
        $data_address = ArrayHelper::map($a_address, function ($el) {
            return $el->id;
        }, function ($el) use (&$data_phones, &$city, &$start) {
            /**
             * @var $el UserAddress
             */
            if ($start == true) {
                $city = $el->city;
            }
            $data_phones[$el->id] = [
                'city' => $el->city,
                'phone' => $el->phone
            ];
            return 'г.' . $el->data_city[$el->city] . ', ул. ' . $el->street . ', дом. ' . $el->home . (($el->house) ? (', кв. ' . $el->house) : '');
        }
        );
        $data_address['none'] = 'Другой';
    }
}

$sum = $i = $sum_normal = 0;
?>
	<style>
        [id*="cp-scrollable"]{
            z-index:99999!important;
        }
    </style>
    <div class="Cart padSpace">
        <a href="<?= Url::to(['site/basket']) ?>" class="backpage"><span>В корзину</span></a>
        <?php
        $model->city = $city;
        if ($user) {
            $model->email = $user->email;
            if (!$no_address) {
                $model->phone = $a_address[0]->phone;
            } else {
                $model->phone = $user->phone;
            }
        }
        $model->code = Yii::$app->session->get('invited_code');
        if (\Yii::$app->user->isGuest) {
//            $model->scenario = 'isGuest';
        } else {
            if ($no_address) {
//                $model->scenario = 'no_address';
            } else {
//                $model->scenario = 'is_address';
            }
        }
        $form = ActiveForm::begin([
            'action' => Url::to(['site/send-form', 'f' => 'order']),
            'enableAjaxValidation' => false,
            'validateOnSubmit' => false,
            'validateOnChange' => false,
            'validateOnBlur' => false,
            'options' => ['enctype' => 'multipart/form-data', 'id' => 'order', 'class' => 'formOrder f_Cart padSpace reverse f_Order'],
            'fieldClass' => ActiveField::className(),
            'fieldConfig' => [
                'required' => false,
                'options' => ['class' => 'col'],
                'template' => <<<HTML
{label}
{input}
HTML
                ,
            ],
        ]);
        ?>
        <!-- <?= $this->render('//blocks/cart_steps') ?>-->
        <h1 class="title">Оформление заказа</h1>
        <div class="customOrTitle"><i>1</i><span>Состав заказа</span></div>
        <div class="cartList" id="cart_list">
            <? if (!$items): ?>
                <div class="cartGoods">
                    <div class="cG_center">
                        <div class="title">
                            Корзина пуста
                        </div>
                    </div>
                </div>
            <? else: ?>
                <?php foreach ($items as $item): ?>
                    <?php
                    $count = $context->cart_items[$item->id];

                    $price = -1;

                    if ($giftsAdded && !empty($giftsAdded[$item->id])) {
                        $sale = \backend\modules\catalog\models\Sales::find()
                            ->select(['id', 'gifts'])
                            ->where(['active' => 1])
                            ->andWhere(['not', ['gifts' => null]])
                            ->andWhere(['id' => $giftsAdded[$item->id]])
                            ->one();

                        if (!empty($sale)) {
                            foreach ($sale->gifts as $gift) {
                                if ($gift['id'] == $item->id) {
                                    $price = $gift['price'];
                                    break;
                                }
                            }
                        }
                    }

                    if ($price > -1) {
                        $item_sum = $price * $count;
                        $sum_normal += $item_sum * $count;
                    }
                    else {
                        $item_sum = $context->function_system->full_item_price($discount, $item, $count, 0, $saleData);
                        $sum_normal += $item->sum_price($count);
                    }

                    $handling = [];
//                    if (isset($type_handling_session[$item->id])) {
//                        $handling = array_flip($type_handling_session[$item->id]);
//                    }
                    $sum += $item_sum;
                    ?>
                    <div class="cartGoods" id="items-<?= $item->id ?>">
                        <a href="<?= $item->url() ?>" class="image" style="background-image: url(<?= $item->img() ?>);">
                        </a>
                        <div class="cG_center">
                            <a class="title" href="<?= $item->url() ?>" target="_blank"><?= $item->name ?></a>
                            <? if ($item->body_small): ?>
                                <div class="descript"><?= $item->body_small ?></div>
                            <? endif ?>
                            <span class="gift-text"><? if ($giftsAdded && !empty($giftsAdded[$item->id])):?>Подарок<? endif;?></span>
                            <? if ($item->weight): ?>
                                <div class="weight">Вес брутто: <?= $item->weight ?> кг</div>
                            <? endif ?>
							<? if ($item->vendor_code): ?>
                                <div class="weight">Артикул: <?= $item->vendor_code ?></div>
                            <? endif ?>
                            <? if (false && $item->itemsTypeHandlings): ?>
                                <div class="string">
                                    <?php
                                    $handing_string = '';
                                    foreach ($item->itemsTypeHandlings as $item_handling) {
                                        if (!$item_handling->typeHandling->isVisible || !isset($handling[$item_handling->type_handling_id])) {
                                            continue;
                                        }
                                        $handing_string .= '<p><span class="ordered">' . $item_handling->typeHandling->name . '</span></p>';
                                    }
                                    if ($handing_string) {
                                        echo $handing_string;
                                    }
                                    ?>
                                </div>
                            <? endif ?>
                        </div>
                        <div class="cG_right">
                            <div class="numSize" data-val="{count} шт."><?= $count ?> шт.</div>
                            <div class="price"><?= number_format($item_sum, 0, '', ' ') ?> 〒</div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <? endif; ?>
            <?
            if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
                $order = new Orders(['discount' => Yii::$app->user->identity->discount . '%']);
                $sum = $sum - $order->discount($sum);
            }

            $sum_full = $sum + (str_replace(' ', '', $delivery) > 0 ? str_replace(' ', '', $delivery) : 0);
            //$delivery = $context->function_system->delivery_price($sum_full, $context->city);

            $discount_price = ($sum_normal - $sum);
            if (!$discount_price) {
                $discount_price = 0;
            }
            ?>
            <!--<div class="customOrTitle"><i>3</i><span>Выберите способ оплаты</span></div>
            <div class="cartGoods">
                <div class="string">
                    <?= $form->field($model, 'payment', [
                        'template' => '{label}<div class="blSelect payment">{input}</div>'
                    ])->dropDownList($model->data_payment); ?>
                </div>
            </div>-->
            <div class="customOrTitle"><i>2</i>
                <? if ($context->function_system->only_pickup): ?>
                    <? $model->type_delivery = 0; ?>
                    <span>Место забора</span>
                <? else: ?>
                    <? $model->type_delivery = 1; ?>
                    <span>Выберите способ доставки</span>
                <? endif; ?>
            </div>
            <div class="cartGoods">
                <div class="string select_address">
                    <?= $form->field($model, 'city', [
                        'template' => '{label}<div class="blSelect">{input}</div>'
                    ])->dropDownList($cityList); ?>
                </div>
                <div class="delivery-list" id="<?=$form_name?>-delivery">
                    <?php
                    $cityDeliveries = Json::decode($deliveryInfo);
                    $cityDelivery = $cityDeliveries[$citySelected];
                    $i = 0;

                    foreach ($cityDelivery['delivery'] as $key => $list) :?>
                        <?php if (($key != 'delivery_method_pickup' && strpos($key, 'delivery_method_courier_') === false
                                && $list['price'] == 0) || $list['output'] == 0) {
                            continue;
                        }?>
                        <div class="row">
                            <div class="col-md-1 delivery-days<?=($list['active'] == 0 ? ' disabled' : '')?>">
                                <?=Html::radio($form_name.'[delivery]', ($i++ == 0 && $list['active'] ? true : false), ['value' => $key, 'disabled' => ($list['active'] == 0 ? true : false)])?>
                                <?=Html::label($list['delivery_method'])?>
                                <p><?=$list['textSelect']?></p>
                                <p><?=$list['days']?></p>
                                <?php if (!empty($list['stories'])): ?>
                                    <div class="popup-block">
                                        <div class="select-city" onclick="popup({block_id: '#popupPickUp', action: 'open'});">
                                            <span>Выбрать пункт</span>
                                        </div>
                                        <div class="overlayWinmod">
                                            <div id="popupPickUp" class="popup window">
                                                <div class="popupClose" onclick="popup({block_id: '#popupPickUp', action: 'close'});"></div>
                                                <p class="popup-header">Выбрать пункт</p>
                                                <ul>
                                                    <?php foreach ($list['stories'] as $story):?>
                                                        <li data-id="<?=$story['id']?>">
                                                            <?=$story['name']?> (<a href="/our-stores?id=<?=$story['city']?>">Посмотреть на карте</a>)
                                                        </li>
                                                    <?php endforeach;?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <p><?=($list['price'] > 0 ? '+'.$list['priceFormat'] : '0')?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?= Html::activeHiddenInput($model, 'type_delivery') ?>
                <?= Html::activeHiddenInput($model, 'only_pickup') ?>
                <?php
                $currentStory = (key($cityDelivery['delivery']) == 'delivery_method_pickup' ? current($cityDelivery['delivery'])['stories'] : []);
                ?>
                <?= Html::activeHiddenInput($model, 'our_stories_id', [
                    'value' => (!empty($currentStory) ? current($currentStory)['id'] : '')
                ]) ?>
                <? if ($model->type_delivery == 1): ?>
                    <? if ($user): ?>
                        <? if ($data_address): ?>
                            <div class="string delivery-address">
                                <label>Адрес доставки</label>
                                <div class="blSelect payment">
                                    <?= Html::activeDropDownList($model, 'address_id', $data_address) ?>
                                </div>
                            </div>
                        <? endif ?>
                    <? endif; ?>
                    <?
                    if (!$no_address) {
                        $class_address = 'hidden';
                        $json_phones = Json::encode($data_phones);
                        $this->registerJs(<<<JS
$("#{$form_name}-address_id").chosen({disable_search_threshold: 10});
var data_phones = {$json_phones}
$("#{$form_name}-address_id").on('change', function (e) {
    changeSelectAddress($(this).val());
})
JS
                        );
                    } else {
                        $class_address = '';
                    }
                    $text_default_pickup = Json::encode($context->settings->get('delivery_text_no_delivery'));
                    ?>
                    <div class="string addr select_address <?= $class_address ?> address_delivery">
                        <?= $form->field($model, 'street', ['options' => ['class' => 'col second']]); ?>
                        <?= $form->field($model, 'home', ['options' => ['class' => 'col third']]); ?>
                        <?= $form->field($model, 'house', ['options' => ['class' => 'col fourth']]); ?>
                    </div>
                    <div class="string text only_pickup only_pickup_text hidden">
                        В вашем городе доступен только самовывоз
                    </div>
                <? else: ?>
                    <?
                    if ($context->city_model && $context->city_model->pickup) {
                        $text_pickup = $context->city_model->pickup;
                    } else {
                        $text_pickup = $context->settings->get('delivery_text_no_delivery');
                    }
                    ?>
                    <div class="string text">
                        <?= $text_pickup ?>
                    </div>
                <? endif; ?>
                <?= $form->field($model, 'comments', ['options' => ['class' => 'string']])->textarea(); ?>
                <div class="string">
                    <?= $form->field($model, 'time_order', [
                        'template' => '{label}<div class="blSelect">{input}</div>'
                    ])->dropDownList($model->time_days); ?>
                </div>
                <div class="altTitle">Укажите контактную информацию</div>
                <? if (!$user): ?>
                    <div class="string twoCol">
                        <?= $form->field($model, 'first_name'); ?>
                        <?= $form->field($model, 'last_name'); ?>
                    </div>
                <? endif ?>
                <div class="string twoCol">
                    <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '+7(999)-999-9999',
                        'definitions' => [
                            'maskSymbol' => '_'
                        ],
                        'options' => [
                            'class' => ''
                        ]
                    ]); ?>
                </div>
                <? if (!$user || ($user && !$user->email)): ?>
                    <div class="string twoCol">
                        <?= $form->field($model, 'email'); ?>
                    </div>
                <? endif ?>
            </div>
			<div class="customOrTitle"><i>3</i><span>Выберите способ оплаты</span></div>
            <div class="cartGoods">
                <div class="string">
                    <?= $form->field($model, 'payment', [
                        'template' => '{label}<div class="blSelect payment">{input}</div>'
                    ])->dropDownList($model->data_payment); ?>
                </div>
            </div>	
        </div>
        <div class="cartRight" id="cart_right">
            <div class="wrapperFixedPosition" id="wrap_fixed">
                <div class="cartOrder">
                    <div class="cartLines">
                        <div class="cartLine">
                            <div class="string promoCode" id="check_promo">
                                <label></label>
                                <input type="text" name="code" placeholder="Промо-код" value="<?= $invited_code ?>">
                                <a href="#" class="btn_Form blue button_check_promo">Активировать</a>
                            </div>
                        </div>
                        <div class="cartLine">
                            <span>Сумма</span>
                            <div class="cL_right"><b
                                        class="basket_sum"><?= number_format($sum + $discount_price, 0, '', ' ') ?>
                                    〒</b></div>
                        </div>
                        <div class="cartLine <?= ($discount_price) ? '' : 'hidden' ?>" id="discount_block">
                            <span>Скидка</span>
                            <div class="cL_right"><b
                                        class="basket_sum_discount"><?= number_format($discount_price, 0, '', ' ') ?>
                                    〒</b></div>
                        </div>
                        <? if ($gifts_count > 0):?>
                            <div class="gifts-cart-text cartLine">
                                <span>Подарки:</span>
                                <div class="cL_right"><?=$gifts_count?> шт.</div>
                            </div>
                        <? endif;?>
                        <div class="cartLine">
                            <span>Доставка</span>
                            <div class="cL_right delivery_price"><?= $delivery ?></div>
                        </div>
                        <div class="cartLine">
                            <span>Итого</span>
                            <div class="cL_right"><b
                                        class="basket_sum_full"> <?= number_format($sum_full, 0, '', ' ') ?> 〒</b></div>
                        </div>
                    </div>
                    <? if (!Yii::$app->user->isGuest && $user->bonus): ?>
                        <div class="string cCheckbox">
                            <?= Html::checkbox(Html::getInputName($model, 'bonus'), $model->bonus, ['id' => Html::getInputId($model, 'bonus'), 'uncheck' => 0]) ?>
                            <label for="<?= Html::getInputId($model, 'bonus') ?>">Использовать накопленные бонусы для
                                оплаты</label>
                        </div>
                    <? endif ?>
                    <div class="cartOrderControl">
                        <button class="btn_Form blue" type="submit">Подтвердить заказ</button>
                    </div>
                    <?d::res()?>
                </div>
                <? if (!Yii::$app->user->isGuest): ?>
                    <div class="cartBalance">
                        <div class="sometext">
                            <?
                            $percent_bonus = $context->function_system->percent();
                            $add_bonus = floor(((int)$sum * ($percent_bonus)) / 100)
                            ?>
                            <p><b>У вас <?= Yii::$app->user->identity->bonus ?> бонусов</b></p>
                            <p>Мы начислим вам <b><?= $add_bonus ?></b> бонусов за этот заказ</p>
                        </div>
                        <br>
                        <?
                        $all_bonus = BonusSettings::all();
                        $start_bonus = true;
                        $text_bonus = '';
                        $number = 0;
                        foreach ($all_bonus as $key_bon => $all_bon) {
                            if ($all_bon->price_start <= $user->order_sum && $all_bon->price_end >= $user->order_sum) {
                                /*if ($start_bonus) {
                                    $text_bonus = 'У вас нет постоянной скидки. <br>';
                                } else {
                                    $text_bonus = 'У вас ' . $all_bon->percent . '% постоянной скидки. <br>';
                                }
                                if (isset($all_bonus[$key_bon + 1])) {
                                    $number = number_format($all_bonus[$key_bon + 1]->price_start - $user->order_sum, 0, '', ' ');
                                    $text_bonus .= <<<HTML
Для получения скидки Вам осталось
                            сделать заказов на сумму <b>{$number} 〒</b>
HTML;
                                }*/
                                break;
                            }
                            $start_bonus = false;
                        }
                        ?>
                        <? if ($text_bonus): ?>
                            <p>
                                <?= $text_bonus ?>
                            </p>
                        <? endif ?>
                    </div>
                <? endif ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php
$url_check_promo = Json::encode(Url::to(['api/cart']));
$this->registerJsFile('https://widget.cloudpayments.kz/bundles/cloudpayments');

$this->registerJs(<<<JS
var city_pickup = {$deliveryInfo};
console.log(city_pickup);

function check_address_user(val) {
    var city = city_pickup[data_phones[val].city];

    if (city.only_pickup == 1) {
        $('.only_pickup').removeClass('hidden');
        $('#{$form_name}-only_pickup').val(1);
        
        $('.only_pickup_text').html('');
    } else {
        $('.only_pickup').addClass('hidden');
        $('#{$form_name}-only_pickup').val(0);
    }
}

function only_pickup() {
    var current_city = city_pickup[$("#{$form_name}-city").val()];
    
    if (current_city.only_pickup == 1) {
        $('#{$form_name}-only_pickup').val(1);
        $('.address_delivery').addClass('hidden');
        $('.only_pickup').removeClass('hidden');
        $('.only_pickup_text').html('');
        $('#order-time_order').closest('.string').addClass('hidden');

    } else {
        $('#{$form_name}-only_pickup').val(0);
        
        for (var i in current_city.delivery) {
            if (i == 'delivery_method_pickup' &&  !$('.address_delivery').hasClass('hidden')) {
                $('.address_delivery').addClass('hidden');
            }
            
            if (i == 'delivery_method_pickup' &&  !$('.delivery-address').hasClass('hidden')) {
                $('.delivery-address').addClass('hidden');
            }
                     
            if (i.indexOf('delivery_method_courier') != -1 &&  $('#order-time_order').closest('.string').hasClass('hidden')) {
                $('#order-time_order').closest('.string').removeClass('hidden');
            }
            
            if (i.indexOf('delivery_method_courier') == -1 &&  !$('#order-time_order').closest('.string').hasClass('hidden')) {
                  $('#order-time_order').closest('.string').addClass('hidden');
            }
            
            break;
        }

    }
}

function changeSelectAddress(value) {
    if (value == 'none') {
        if ($('.address_delivery').hasClass('hidden')) {
            $('.address_delivery').removeClass('hidden');
        }
    } else {
        check_address_user(value);
        $("#{$form_name}-phone").val(data_phones[value].phone);
        
        $('#{$form_name}-only_pickup').val(1);
        $('.only_pickup').removeClass('hidden');
        $('.only_pickup_text').html('');
        $('#order-time_order').closest('.string').addClass('hidden');
        
        if (!$('.address_delivery').hasClass('hidden')) {
            $('.address_delivery').addClass('hidden');
        }
    }
}

if ($("#{$form_name}-city").is(':visible')) {
    only_pickup();
} else {
    check_address_user($("#{$form_name}-address_id").val());
}

$("#{$form_name}-city").on('change', function (e) {
    var cityId = $(this).val();
    var current_city = city_pickup[cityId];
    
    if (current_city.checkDelivery == 0) {
        var data = {
            'id' : cityId
        };
        
        $('#loader').show();
        $('#loader img').show();
            
        $.ajax({
            url: '/delivery-city',
            type: 'POST',
            dataType: 'JSON',
            data: data,
            success: function (data) {
                $('#loader').hide();
                $('#loader img').hide();
                city_pickup[cityId] = data;
                changeDeliveries(data);
                changePayments(data);
            },
            error: function () {
                $('#loader').hide();
                $('#loader img').hide();
                $.growl.error({title: 'Ошибка', message: "Произошла ошибка на стороне сервера!", duration: 5000});
            }
        });
    }
    else {
        changeDeliveries(current_city);
        changePayments(current_city);
    }
});

function changeDeliveries(current_city)
{
    if (current_city.only_pickup != 1) {
        $('.address_delivery').removeClass('hidden');
        $('.only_pickup').addClass('hidden');
        $('#{$form_name}-only_pickup').val(0);
    } else {
        $('#{$form_name}-only_pickup').val(1);
        $('.only_pickup').removeClass('hidden');
        
        $('.only_pickup_text').html('');
        
        $('#cart_right .cartOrder .delivery_price').html('0');
        $('#cart_right .cartOrder .basket_sum_full').html($('#cart_right .cartOrder .basket_sum').html());
    }
    
    if (current_city.delivery) {
        var k = 0;
        
        $("#{$form_name}-delivery").html('');
        var html = '';
            
        for (var i in current_city.delivery) {
            if ((i.indexOf('delivery_method_courier_') == -1 && i != 'delivery_method_pickup' && 
                current_city.delivery[i].price == 0) || current_city.delivery[i].output == 0) {
                continue;
            }
            
             html += '<div class="row">'+
                        '<div class="col-md-1 delivery-days';
             
             if (current_city.delivery[i].active == 0) {
                 html += ' disabled';
             }
             
             html += '">'+
                            '<input type="radio" name="{$form_name}[delivery]" value="'+ i +'"'
             if (k == 0) {
                 html += ' checked=""';
             }
             
             if (current_city.delivery[i].active == 0) {
                 html += ' disabled=""';
             }
             
             html += '>' +
                '<label>'+ current_city.delivery[i].delivery_method +'</label>'+
                            '<p>'+ current_city.delivery[i].textSelect +'</p>'+
                            '<p>'+ current_city.delivery[i].days +'</p>';
             if (current_city.delivery[i].stories) {
                var stores = current_city.delivery[i].stories;
                var m = 0;
                         
                for (var j in stores) {
                    if (m++ == 0) {
                        html += '<div class="popup-block">'+
                            '<div class="select-city" onclick="popup({block_id: \'#popupPickUp\', action: \'open\'});">'+
                                   '<span>Выбрать пункт</span>'+
                            '</div>'+
                            '<div class="overlayWinmod">'+
                            '<div id="popupPickUp" class="popup window">'+
                                  '<div class="popupClose" onclick="popup({block_id: \'#popupPickUp\', action: \'close\'});"></div>'+
                                 '<p class="popup-header">Выбрать пункт</p>'+
                                  '<ul>';
                        
                        if (k == 0 && i == 'delivery_method_pickup') {
                            $('#order-our_stories_id').val(j);
                        }
                        
                        if (k == 0 && i != 'delivery_method_pickup') {
                            $('#order-our_stories_id').val('');
                        }
                    }
                    html += '<li data-id="'+j+'">' +
                              stores[j].name+' (<a href="/our-stores?id='+stores[j].city+'">Посмотреть на карте</a>)'+
                         '</li>';
                 }
                
                if (m > 0) {
                    html += '</div></div></div>';
                }
             }
             
             html += '</div>'+
                        '<div class="col-md-2">'+
                            '<p>';
             if (current_city.delivery[i].price > 0) {
                 html += '+'+current_city.delivery[i].priceFormat;
             }
             else {
                 html += '0';
             }
             
             html += '</p>'+
                        '</div>'+
                    '</div>';
             
             if (k ++ == 0) {
                 changeAddress(i);
                 
                 $('#cart_right .cartOrder .delivery_price').html(current_city.delivery[i].text);
                 $('#cart_right .cartOrder .basket_sum_full').html(current_city.delivery[i].sum_all);
             }
        }
        
        $("#{$form_name}-delivery").append(html);
        $("#{$form_name}-delivery").trigger("on");
        $('#order-our_stories_id').val('');
    }
    else {
        $("#{$form_name}-delivery").hide();
    }
}

function changePayments(current_city)
{
    $('#order-payment option').remove();
    
    if (typeof current_city.payments != 'undefined') {
        for (var i in current_city.payments) {
            if (current_city.payments[i].length > 0) {
                $("#order-payment")
                 .append($("<option></option>")
                            .attr("value", i)
                            .text(current_city.payments[i])
                 );
            }
        }
        
        $("#order-payment").trigger("chosen:updated");
    }
}

function changeAddress(val) {
    if (val == 'delivery_method_pickup') {
        $('#{$form_name}-only_pickup').val(1);
        
        if ($('.only_pickup_text').hasClass('hidden') && $('#order-our_stories_id').val().length > 0) {
            //$('.only_pickup_text').removeClass('hidden');
        }
        
        if (!$('.delivery-address').hasClass('hidden')) {
            $('.delivery-address').addClass('hidden');
        }
            
        if (!$('.address_delivery').hasClass('hidden')) {
            $('.address_delivery').addClass('hidden');
        }
        
        $('#order-time_order').closest('.string').addClass('hidden');
    }
    else {
        if (!$('.only_pickup_text').hasClass('hidden')) {
            $('.only_pickup_text').addClass('hidden');
        }
        
        if (val.indexOf('delivery_method_courier') != -1) {
            if ($('#order-time_order').closest('.string').hasClass('hidden')) {
                $('#order-time_order').closest('.string').removeClass('hidden');
            }
        }
        else {
            if (!$('#order-time_order').closest('.string').hasClass('hidden')) {
                $('#order-time_order').closest('.string').addClass('hidden');
            }
        }
        
        if ($('.delivery-address').hasClass('hidden')) {
            $('.delivery-address').removeClass('hidden');
        }
        
        if ($('#order-address_id').length == 0) {
            if ($('.address_delivery').hasClass('hidden')) {
                $('.address_delivery').removeClass('hidden');
            }
        }
        else {
            if (!$('.address_delivery').hasClass('hidden')) {
                $('.address_delivery').addClass('hidden');
            }
        }
    }
}

$(document).on('click', "#{$form_name}-delivery label", function (e) {
    var input = $(this).prev();
    
    if (input.prop('checked') == true || input.prop('disabled') == true) {
        //input.prop('checked', false);
    }
    else {
        input.prop('checked', true);
        
        var current_city = city_pickup[$("#{$form_name}-city").val()];
        
        changeAddress(input.val());
        
        $('#cart_right .cartOrder .delivery_price').html(current_city.delivery[input.val()].text);
        $('#cart_right .cartOrder .basket_sum_full').html(current_city.delivery[input.val()].sum_all);
    }
});
JS
);


$this->registerJs(<<<JS
$('.button_check_promo').on('click', function (e) {
    e.preventDefault();
    sendPromoCode();
})
if ($('input', '#check_promo').val()!=''){
  sendPromoCode();
} 
function sendPromoCode(){
  var element = $('input', '#check_promo');
    if ($.trim(element.val()) == '') {
        $('#check_promo').addClass('error');
        if (!$(element).data('tooltipster-ns')) {
            $(element).tooltipster({
                content: 'Поле не заполнено!'
            });
        } else {
            $(element).tooltipster('content', 'Поле не заполнено!');
            $(element).tooltipster('enable');
        }
    } else {
        var request_data = $('input', '#check_promo').serializeArray();
        var add_data = {name: 'action', value: 'check_promo'};
        request_data.push(add_data);
        request_data.push({name: 'city', value: {$context->city}});
        $('#check_promo').removeClass('error');
        if ($(element).data('tooltipster-ns')) {
            $(element).tooltipster('disable');
        }
        $('#loader').show();
        $.ajax({
            url: {$url_check_promo},
            type: 'GET',
            dataType: 'JSON',
            data: request_data,
            success: function (data_return) {
                $('#loader').hide();
                if (typeof data_return.errors != 'undefined') {
                    $('#check_promo').addClass('error');
                    if (!$(element).data('tooltipster-ns')) {
                        $(element).tooltipster({
                            content: data_return.errors
                        });
                    } else {
                        $(element).tooltipster('content', data_return.errors);
                        $(element).tooltipster('enable');
                    }
                } else {
                    $('.basket_sum').text(data_return.sum);
                    $('.delivery_price').html(data_return.delivery);

                    $('.basket_sum_full').text(data_return.sum_full);
                    if (typeof data_return.discount_price != 'undefined') {
                        $('.basket_sum_discount').html(data_return.discount_price);
                        if ($('#discount_block').hasClass('hidden')) {
                            $('#discount_block').removeClass('hidden')
                        }
                        $.growl.notice({title: '', message: "Промо-код активирован", duration: 5000});
                    } else {
                        if (!$('#discount_block').hasClass('hidden')) {
                            $('#discount_block').addClass('hidden')
                        }
                    }
                }
            },
            error: function () {
                $('#loader').hide();
                $.growl.error({title: 'Ошибка', message: "Произошла ошибка на стороне сервера!", duration: 5000});
            }
        });
    }
}
$("#{$form_name}-city").chosen({disable_search_threshold: 10});
$("#{$form_name}-address_id").chosen({disable_search_threshold: 10});
$("#{$form_name}-time_order").chosen({disable_search_threshold: 10});
$("#{$form_name}-payment").chosen({disable_search_threshold: 10});
//$("#{$form_name}-delivery").chosen({disable_search_threshold: 10});
cart_fixed_block();

$(document).on('click', "#popupPickUp ul li a", function (e) {
    e.preventDefault();
    window.open(e.target.href, '_blank');
});

$(document).on('click', "#popupPickUp ul li", function (e) {
    e.preventDefault();
    
    var current_city = city_pickup[$("#{$form_name}-city").val()];
    var stories = current_city.delivery.delivery_method_pickup.stories;
    var idStory = $(this).attr('data-id');
    
    if (stories[idStory].address != '') {
        $('.only_pickup_text').html(stories[idStory].address);
        $('.only_pickup_text').removeClass('hidden');
    } 
    else {
        if (current_city.text_pickup != '') {
            $('.only_pickup_text').html(current_city.text_pickup);
        }
        else {
            $('.only_pickup_text').html({$text_default_pickup});
        }
    }
    
    $('#order-our_stories_id').val(idStory);
    
    popup({block_id: '#popupPickUp', action: 'close'});
});

JS
);
/*
if (!empty($deliveryList)) {
    $this->registerJs(<<<JS
$("#{$form_name}-delivery").chosen({disable_search_threshold: 10});

JS
    );
}
else {
    $this->registerJs(<<<JS
$("#{$form_name}-delivery").closest('.field-order-delivery').hide();
JS
    );
}*/

$this->registerCss(<<<CSS
.gift-text {
    color: #ea6815;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
}

form.f_Cart .cartRight .cartOrder .cartLine.gifts-cart-text span, form.f_Cart .cartRight .cartOrder .cartLine.gifts-cart-text .cL_right {
    color: #ea6815;
}

CSS
    , ['type' => 'text/css']);
?>