<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $items Items[]
 * @var $sets Sets[]
 * @var $address \common\models\UserAddress[]
 */

use common\components\Debugger as d;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Orders;
use backend\modules\catalog\models\Sales;
use backend\modules\catalog\models\Sets;
use common\models\BonusSettings;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

//d::pex(Yii::$app->request->cookies);

$context = $this->context;
$sets = $discount = [];

if ($cartItems) {
    if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
        $discount = [];
    } else {
        $discount = $context->function_system->discount_sale_items($items, $cartItems);
    }
}

//$type_handling = Yii::$app->session->get('type_handling', []);
$type_handling = Yii::$app->c_cookie->get('type_handling', []);
$address = [];
$sum = $sum_normal = 0;
$i = 0;
$is_weight = false;

//$giftsAdded = Yii::$app->session->get('gifts', []);
$giftsAdded = Yii::$app->c_cookie->get('gifts', []);
?>

<div class="Cart padSpace">
	<a href="/" class="backpage"><span>Вернуться к покупкам</span></a>
    <?= Html::beginForm(['/site/order'], 'post', ['class' => 'f_Cart padSpace reverse', 'id' => 'basket_form']) ?>
    <!-- <?= $this->render('//blocks/cart_steps') ?>-->
    <h1 class="title">Корзина</h1>
    <div class="cartList" id="cart_list">
        <? if (!$items && !$sets): ?>
            <div class="cartGoods">
                <div class="cG_center">
                    <div class="title">
                        В вашей корзине пусто. 
                    </div>
                </div>
            </div>
        <? else: ?>
			<a href="#presents" class="go-presents no-link">Получить подарок</a>
            <?php foreach ($items as $item): ?>
                <?php
                $count = $cartItems[$item->id];
                $price = -1;

                if ($giftsAdded && !empty($giftsAdded[$item->id])) {
                    $sale = Sales::find()
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
                    $sum_normal += $item->real_sum_price($count);
                }

                $sum += $item_sum;
                ?>
                <div class="cartGoods" id="items-<?= $item->id ?>">
                    <a href="<?= $item->url() ?>" class="image" style="background-image: url(<?= $item->img() ?>);">
                        <!--<? if ($item->vendor_code): ?>
                            <span>Арт. <?= $item->vendor_code ?></span>
                        <? endif ?>-->
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
                                <?php foreach ($item->itemsTypeHandlings as $item_handling): ?>
                                    <?php
                                    $checked = false;
                                    if (!$item_handling->typeHandling->isVisible) {
                                        continue;
                                    }
                                    $checked = (isset($type_handling[$item->id]) && in_array($item_handling->typeHandling->id, $type_handling[$item->id]));
                                    ?>
                                    <p>
                                        <input type="radio"
                                               value="<?= $item_handling->typeHandling->id ?>" <?= ($checked) ? 'checked' : '' ?>
                                               id="item_handling_<?= $item_handling->id ?>"
                                               name="type_handling[<?= $item->id ?>][]">
                                        <label for="item_handling_<?= $item_handling->id ?>"><?= $item_handling->typeHandling->name ?></label>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        <? endif ?>
                        <!--<div class="string">
                            <span class="cG_delete delete_basket" data-id="<?= $item->id ?>">Удалить</span>
                        </div>-->
                    </div>
                    <div class="cG_right">
                        <? if ($giftsAdded && !empty($giftsAdded[$item->id])):?>
                            <div class="inputWrapper gift-count">1</div>
                            <input type="hidden" value="<?= $count ?>" name="items[<?= $item->id ?>]"
                                   data-type="items" data-id="<?= $item->id ?>" data-measure="<?= $item->measure ?>"/>
                        <?else:?>
                            <div class="inputWrapper">
                                <?goto c_t;?>
                                <div class="btnMinus"></div>
                                <div class="btnPlus"></div>
                                <input type="text" value="<?= $count ?>" name="items[<?= $item->id ?>]"
                                       data-type="items"
                                       data-id="<?= $item->id ?>"
                                       data-measure="<?= $item->measure ?>"
                                       readonly=""/>
                                <!--<span>шт.</span>-->
                                <?c_t:?>
                                <label class="label">Количество</label>
                                <select
                                    name="items[<?= $item->id ?>]"
                                    id="product_count"
                                    data-type="items"
                                    data-id="<?=$item->id?>"
                                    data-count="<?=$count?>"
                                    data-measure="<?= $item->measure ?>"
                                    readonly=""
                                    class="product-count b-p trigger_demo2"
                                >
                                    <?for($ct=0;$ct<101;$ct++):?>
                                        <option <?=($count==$ct)?'selected':''?> value="<?=$ct?>"><?=$ct?></option>
                                    <?endfor?>
                                </select>
                            </div>
                        <? endif;?>
                        <div class="price"><?= number_format($item_sum, 0, '', ' ') ?> 〒</div>
						
                    </div>
					<div class="string del">
             				<span class="cG_delete delete_basket" data-id="<?= $item->id ?>">Удалить</span>
      		  		</div>
                </div>
            <?php endforeach; ?>
        <? endif; ?>
    </div>
    <? if ($items || $sets): ?>
        <?php
        if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
            $order = new Orders(['discount' => Yii::$app->user->identity->discount . '%']);
            $sum = $sum - $order->discount($sum);
        }
        $sum_full = $sum;
        $delivery = $context->function_system->delivery_price($sum_full, $context->city);
        $discount_price = ($sum_normal - $sum);
        if (!$discount_price) {
            $discount_price = 0;
        }
        ?>
        <div class="cartRight" id="cart_right">
            <div class="wrapperFixedPosition" id="wrap_fixed">
                <div class="cartOrder">
                    <div class="cartLines">
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
                        <!--div class="cartLine">
                            <span>Доставка</span>
                            <div class="cL_right delivery_price"><?/*= $delivery */?></i></div>
                        </div-->
                        <!--div class="cartLine">
                            <span>Итого</span>
                            <div class="cL_right"><b
                                        class="basket_sum_full"> <?//= number_format($sum, 0, '', ' ') ?> 〒</b></div>
                        </div-->
                        <div class="cartLine text_is_weight <?= ($is_weight ? '' : 'hidden') ?>"><?= $context->settings->get('delivery_text_weight') ?></div>
                        <? if ($gifts_count > 0):?>
                            <div class="gifts-cart-text cartLine">
                                <span>Подарки:</span>
                                <div class="cL_right"><?=$gifts_count?> шт.</div>
                            </div>
                        <? endif;?>
                    </div>
                    <div class="cartOrderControl">
                        <button class="btn_Form blue" type="submit">Оформить заказ</button>
                    </div>
                </div>
                <? if (!Yii::$app->user->isGuest): ?>
                    <div class="cartBalance">
                        <div class="sometext">
                            <p><b>У Вас <?= (int)$context->user->bonus ?> бонусов</b></p>
                            <?
                            $percent_bonus = $context->function_system->percent();
                            $add_bonus = floor(((int)$sum * ($percent_bonus)) / 100)
                            ?>
                            <p>За покупку будет начислено <b class="add_bonus"><?= $add_bonus ?></b> бонусов</p>
                        </div>
                    </div>
                <? endif ?>

                <!--<? if (Yii::$app->user->isGuest): ?>
                    <div class="cartBlock" id="fast_cart_order">
                        <div class="title">Быстрый заказ</div>
                        <div class="text">Наш менеджер свяжется с вами в ближайшее время</div>
                        <div class="string" id="fast_order_phone_cart">
                            <label>Ваш телефон</label>
                            <?= MaskedInput::widget([
                                'name' => 'fast_order[phone]',
                                'mask' => '+7(999)-999-9999',
                                'definitions' => [
                                    'maskSymbol' => '_'
                                ],
                                'options' => [
                                    'class' => ''
                                ]
                            ]); ?>
                        </div>
                        <div class="string">
                            <span class="btn_buyToClick fast_cart_order">Купить в 1 клик</span>
                        </div>
                    </div>
                <? endif ?>-->
            </div>
        </div>
    <? endif; ?>
    <?= Html::endForm() ?>
    <div class="gift-blocks">
        <?php if ($gifts):?>
            <?= $this->render('//blocks/cart_gifts', [
                'gifts' => $gifts,
                'md' => $md,
                'cartUrl' => $cartUrl
            ]) ?>
        <?php endif;?>
    </div>
</div>
<?php
$url_cart = Url::to(['site/cart']);
$url_api_cart = Url::to(['api/cart']);
$url_cart_fast = Url::to(['site/send-form', 'f' => 'fast_order']);
$this->registerJs(<<<JS
$('.fast_cart_order').on('click', function (e) {
    e.preventDefault();
    var element = $('input', '#fast_order_phone_cart');
    if ($.trim(element.val()) == '') {
        $('#fast_order_phone_cart').addClass('error');
        if (!$(element).data('tooltipster-ns')) {
            $(element).tooltipster({
                content: 'Необходимо заполнить телефон!'
            });
        } else {
            $(element).tooltipster('content', 'Необходимо заполнить телефон!');
            $(element).tooltipster('enable');
        }
    } else {
        var request_data = $('input', '#fast_cart_order').serializeArray();
        var add_data = {name: 'fast_order[type]', value: '2'};
        request_data.push(add_data);
        $('#fast_order_phone_cart').removeClass('error');
        if ($(element).data('tooltipster-ns')) {
            $(element).tooltipster('disable');
        }
        $('#loader').show();
        $.ajax({
            url: '{$url_cart_fast}',
            type: 'POST',
            dataType: 'JSON',
            data: request_data,
            success: function (data_return) {
                if (typeof data_return.errors != 'undefined') {
                    var errors = data_return.errors;
                    if ($.isArray(errors['fast_order-phone'])) {
                        $('#fast_order_phone_cart').addClass('error');
                        if (!$(element).data('tooltipster-ns')) {
                            $(element).tooltipster({
                                content: errors['fast_order-phone'][0]
                            });
                        } else {
                            $(element).tooltipster('content', errors['fast_order-phone'][0]);
                            $(element).tooltipster('enable');
                        }
                        $.growl.error({title: 'Ошибка', message: errors['fast_order-phone'][0], duration: 5000});
                    } else {
                        $.growl.error({title: 'Ошибка', message: "Произошла ошибка на стороне сервера!", duration: 5000});
                    }
                    $('#loader').hide();
                }
                if (typeof data_return.js != 'undefined') {
                    eval(data_return.js)
                }
                if (typeof data_return.url != 'undefined') {
                    window.location = data_return.url;
                }
            },
            error: function () {
                $('#loader').hide();
                $.growl.error({title: 'Ошибка', message: "Произошла ошибка на стороне сервера!", duration: 5000});
            }
        });
    }

})
$('#basket_form').on('click', '.delete_basket', function (e) {
    var id = $(this).data('id');
    $(this).parents('.cartGoods').remove();
    update_order_price()
}).on('change', 'input[data-id]', function (e) {
    update_order_price()
});
$('.inputWrapper').on('click', '.btnPlus', function (e) {
    var inp = $('input', $(this).parents('.inputWrapper'));
    var inpVal = $(inp).val();
    var measure = $(inp).data('measure');
    var id = $(inp).data('id');
    if (typeof measure == 'undefined' || measure == 1) {
        $(inp).val(+inpVal + 1);
    } else if (measure == 0) {
        var float = /^(\d+\.0)$/;
        var val = parseFloat(+inpVal) + 0.1;
        val = val.toFixed(1);
        if (float.test(val)) {
            val = parseInt(val);
        }
        $(inp).val(val);
    }
    update_order_price()

}).on('click', '.btnMinus', function (e) {
    var inp = $('input', $(this).parents('.inputWrapper'));
    var inpVal = $(inp).val();
    var measure = $(inp).data('measure');
    var id = $(inp).data('id');
    if (typeof measure == 'undefined' || measure == 1) {
        if (inpVal > 1) {
            $(inp).val(+inpVal - 1);
        }
    } else if (measure == 0) {
        if (inpVal > 0.1) {
            var float = /^(\d+\.0)$/;
            var val = parseFloat(+inpVal) - 0.1;
            val = val.toFixed(1);
            if (float.test(val)) {
                val = parseInt(val);
            }
            $(inp).val(val);
        }
    }
    update_order_price()

}).on('change', 'input', function (e) {
    var measure = $(this).data('measure');
    var val = $(this).val();
    var inpVal = $(this).val();
    var id = $(this).data('id');
    if (typeof measure == 'undefined' || measure == 1) {
        if (inpVal > 1) {
            var float_no = /^(\d+\.\d+)$/;
            if (float_no.test(val)) {
                val = parseInt(val);
                $(this).val(val);
            }
        } else {
            val = 1;
            $(this).val(val);
        }
    } else if (measure == 0) {
        if (inpVal > 0.1) {
            var float = /^(\d+\.0)$/;
            val = parseFloat(+inpVal);
            val = val.toFixed(1);
            if (float.test(val)) {
                val = parseInt(val);
                $(this).val(val);
            }
        } else {
            val = 0.1;
            $(this).val(val);
        }
    }
    update_order_price()

});

$('.go-presents').on('click',function(){

	if($(document).width() >= 1000){
		var margin_top = 265;
	}else{
		var margin_top = 160;
	}
	
	var ps = $('#presents');
	
	if(typeof ps === 'undefined') return;
	
    // Сначала получаем позицию элемента(якоря) относительно документа
    var scrollToElement = ps.offset().top;
    // скроллим страницу на значение равное позиции элемента(якоря)
    $('html, body').animate({ scrollTop: (scrollToElement-margin_top) }, 700);
});

$('.inputWrapper').on('click','.dropcontainer_demo2 ul li',function(){
    update_order_price();
});

function update_order_price() {
    var res = $('.res');
    var request_data = $('#basket_form').serializeArray();
    var add_data = {name: 'action', value: 'editBasket'};
    request_data.push(add_data);
    request_data.push({name: 'city', value: {$context->city}});
    $.ajax({
        url: '{$url_api_cart}',
        type: 'GET',
        dataType: 'JSON',
        data: request_data,
        success: function (data) {
            res.html('Done'+JSON.stringify(data));
            if (!data.count) {
                location.reload();
            } else {
                /*edit_basket(data.items, 'items');
                edit_basket(data.sets, 'sets');
                $('.basket_sum').text(data.sum);
                $('.basket_sum_full').text(data.sum_full);
                $('.delivery_price').html(data.delivery);
                $('.add_bonus').text(data.add_bonus);
                if (typeof data.discount_price != 'undefined') {
                    $('.basket_sum_discount').html(data.discount_price);
                    if ($('#discount_block').hasClass('hidden')) {
                        $('#discount_block').removeClass('hidden')
                    }
                } else {
                    if (!$('#discount_block').hasClass('hidden')) {
                        $('#discount_block').addClass('hidden')
                    }
                }*/
                location.reload();
            }
        },
        error: function (data) {
            res.html('Fail'+JSON.stringify(data));
        }
    });
}
function edit_basket(items, type) {
    $.each(items, function (id, el) {
        if (typeof el.price_full != 'undefined') {
            $('.price', '#' + type + '-' + id).html(el.price_full);
        }
    })
}

// Фиксирование блока
cart_fixed_block();
JS
);

$this->registerCss(<<<CSS
.inputWrapper.gift-count {
    font-size: 20px;
}

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
