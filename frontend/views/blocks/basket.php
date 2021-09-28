<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $items Items
 */

use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Orders;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$context = $this->context;
$items = $discount = [];
if ($context->cart_items) {
    $q = new ActiveQuery(Items::className());
    $q->indexBy('id')
        ->andWhere(['id' => array_keys($context->cart_items)]);
    $items = $q->all();
    if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
        $discount = [];
    } else {
        $discount = $context->function_system->discount_sale_items($items, $context->cart_items);
    }
};

$sum = $sum_normal = 0;
$content_items = '';
foreach ($items as $item) {
    $count = $context->cart_items[$item->id];
    $content_items .= $this->render('//blocks/item_cart', ['item' => $item, 'count' => $count]);
    $sum += $context->function_system->full_item_price($discount, $item, $count);
    $sum_normal += $item->sum_price($count);
}
if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
    $order = new Orders(['discount' => Yii::$app->user->identity->discount . '%']);
    $sum = $sum - $order->discount($sum);
}
$sum_full = $sum;
$delivery = 0;
if ($sum_full > 0) {
    $delivery = $context->function_system->delivery_price($sum_full, $context->city);
}
$discount_price = ($sum_normal - $sum);
if (!$discount_price) {
    $discount_price = 0;
}
$percent_bonus = $context->function_system->percent();
$add_bonus = floor(((int)$sum * ($percent_bonus)) / 100)
//{n, plural, one{<b>#</b> товар} few{<b>#</b> товара} many{<b>#</b> товаров} other{<b>#</b> товара}}
?>
<div class="topCart" id="cartWindow">
    <div class="wrapperOverlay"></div>
    <div class="header__basket"><span><i class="cart_count_int"><?= $context->cart_count ?></i></span></div>
    <div class="wrapperClick1"></div>
    <div class="popup" id="modalCartWindow">
        <div class="topTitle">
            <div class="line cart_count_string"><?= Yii::t('shadow', 'count_items', ['n' => $context->cart_count]) ?> на
                сумму <b class="cart_sum_string"><?= number_format($sum, 0, '', ' ') ?> 〒</b></div>
        </div>
        <div class="wrapperScroll" id="cart_items">
            <?= $content_items ?>
        </div>
        <div class="bottomTitle">
            <!--<div class="result">
                <div class="ttRR">Всего</div>
                <div class="price cart_sum_string"><?= number_format($sum + $discount_price, 0, '', ' ') ?> 〒</div>
            </div>-->
            <div class="result <?= ($discount_price) ? '' : 'hidden' ?>">
                <div class="ttRR">Скидка</div>
                <div class="price cart_small_discount"><?= number_format($discount_price, 0, '', ' ') ?> 〒</div>
            </div>
            <div class="result">
                <div class="ttRR">Итого</div>
                <div class="price cart_small_full"><?= number_format($sum_full, 0, '', ' ') ?> 〒</div>
            </div>
            <div class="result">
                <div class="ttRR">Бонусы за заказ</div>
                <div class="price cart_small_add_bonus"><?= number_format($add_bonus, 0, '', ' ') ?></div>
            </div>
            <a href="<?= Url::to(['site/basket']) ?>" class="btn_Form blue">Перейти в корзину</a>
        </div>
    </div>
</div>
<?
$url_cart = Url::to(['site/cart', 'cart_small' => 1]);
$is_basket_big = Json::encode(($context->id == 'site' && $context->action->id == 'basket'));
$this->registerJs(<<<JS
$('#modalCartWindow .wrapperScroll').mCustomScrollbar({
    theme: "3d-cart",
    scrollInertia: 0,
    mouseWheel: {preventDefault: true},
    documentTouchScroll: true
    //advanced: { updateOnContentResize: true }
});
$('.radio_cart_handling').on('change', function (e) {
    var data_form = $(this).closest('.cartBlock').find('input[type=radio]').serializeArray();
    $.each(data_form,function(i,el){
        data_form[i].name='type_handling[]'
    })
    data_form.push({name: 'action', value: 'type_handling'});
    data_form.push({name: 'id', value: $(this).data('id')});
    $.ajax({
        url: '{$url_cart}',
        type: 'GET',
        dataType: 'JSON',
        data: data_form,
        success: function (data) {
        },
        error: function () {

        }
    });
})
$('#send_type_handling').on('click', function (e) {
    e.preventDefault();
    var data_form = $('#form_type_handling').serializeArray();
    data_form.push({name: 'action', value: 'type_handling'});
    $.ajax({
        url: '{$url_cart}',
        type: 'GET',
        dataType: 'JSON',
        data: data_form,
        success: function (data) {
            if (typeof data.js != 'undefined') {
                eval(data.js);
            }
            popup({block_id: '#popupGoodsProcessing', action: 'close'});
            $('#popup_type_handling').html('');
            setTimeout(function () {
                popup({block_id: '#popupOrderByClick', action: 'open'});
            }, 700);
        },
        error: function () {

        }
    });
});
$('.topCart .header__basket').on('click', function(){
    window.location.href = '/basket';
    e.preventDefault();
    return false;
});
$('#cartWindow').on('click', '.delGoods', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var count = $(this).data('count');
    var request_data = {id: id};
    var type = $(this).data('type');
    if (type == 'item') {
        request_data['action'] = 'del';
        var button_add = $('.addCart[data-id=' + id + ']')
        if (!$(button_add).data('text')) {
            $(button_add).text('В корзину');
        } else {
            $(button_add).text($(button_add).data('text'));
        }
    } else {
        request_data['action'] = 'del_set';
    }
    $(this).closest('div.cartBlock').remove();
    $.ajax({
        url: '{$url_cart}',
        type: 'GET',
        dataType: 'JSON',
        data: request_data,
        success: function (data) {
            update_cart(data.items);
            update_cart(data.sets, 'set');
            update_cart_small(data);
            if ($is_basket_big) {
                if (type == 'set') {
                    $('#sets-' + id, '#cart_list').remove()
                } else {
                    $('#items-' + id, '#cart_list').remove()
                }
                update_order_price()
            }
        },
        error: function () {

        }
    });
});
$('body').on('click', '.addCart', function (e) {
    e.preventDefault();
    var res = $('.res');
    if ($(this).hasClass('disable') == false) {
        var id = $(this).data('id');
        var count = $(this).data('count');
        //$(this).addClass('disable');
        $('.addCart[data-id="' + id + '"]').text('В корзине');
        $.ajax({
            url: '{$url_cart}',
            type: 'GET',
            dataType: 'JSON',
            data: {id: id, count: count, action: 'add'},
            success: function (data) {
                res.html('Done<br>'+JSON.stringify(data));
                $('.addCart[data-id="' + id + '"]').addClass('__in-cart');
                update_cart(data.items);
                update_cart_small(data);
                if (data.price_delivery_popup != 0) {
                    $('#min_sum_delivery_popup').html(data.min_sum_delivery)
                    $('#price_delivery_popup').html(data.price_delivery_popup)
                    if ($('#text_delivery_popup').hasClass('hidden')) {
                        $('#text_delivery_popup').removeClass('hidden');
                    }
                } else {
                    if (!$('#text_delivery_popup').hasClass('hidden')) {
                        $('#text_delivery_popup').addClass('hidden');
                    }
                }
                if (typeof data.type_handling != 'undefined') {
                    $('#popup_type_handling').html(data.type_handling);
                    popup({block_id: '#popupGoodsProcessing', action: 'open'});
                } else {
                    //window.location.href = '/basket';
                    //popup({block_id: '#popupOrderByClick', action: 'open'});
					listen_cart();
                }
            },
            error: function (data) {
                res.html('Fail<br>'+JSON.stringify(data));
            }
        });
    } else {
//				location.replace($('#cart a').attr('href'));
    }
}).on('click', '.fastCart', function (e) {
    e.preventDefault();
    $('#fast_order-type', '#fastOrder').val(1);
    $('#fast_order-items', '#fastOrder').val($(this).data('id'));
    popup({block_id: '#fastOrder', action: 'open'});
}).on('click', '.addSets', function (e) {
    e.preventDefault();
    if ($(this).hasClass('disable') == false) {
        var id = $(this).data('id');
        var count = 1;
        $(this).text('Добавлен в корзину');
        $.ajax({
            url: '{$url_cart}',
            type: 'GET',
            dataType: 'JSON',
            data: {id: id, count: count, action: 'add_set'},
            success: function (data) {
                update_cart(data.sets, 'set');
                update_cart_small(data);
                if (data.price_delivery_popup != 0) {
                    $('#min_sum_delivery_popup').html(data.min_sum_delivery)
                    $('#price_delivery_popup').html(data.price_delivery_popup)
                    if ($('#text_delivery_popup').hasClass('hidden')) {
                        $('#text_delivery_popup').removeClass('hidden');
                    }
                } else {
                    if (!$('#text_delivery_popup').hasClass('hidden')) {
                        $('#text_delivery_popup').addClass('hidden');
                    }
                }
                popup({block_id: '#popupOrderByClick', action: 'open'});
            },
            error: function () {

            }
        });
    } else {
//				location.replace($('#cart a').attr('href'));
    }
}).on('click', '.fastSets', function (e) {
    e.preventDefault();
    $('#fast_order-type', '#fastOrder').val(3);
    $('#fast_order-items', '#fastOrder').val($(this).data('id'));
    popup({block_id: '#fastOrder', action: 'open'});
}).on('click', '.add_discount', function (e) {
    e.preventDefault();
    if ($(this).hasClass('disable') == false) {
        var id = $(this).data('id-item');
        var count = 1;
        $(this).text('Добавлен в корзину');
        $.ajax({
            url: '{$url_cart}',
            type: 'GET',
            dataType: 'JSON',
            data: {id: id, count: count, action: 'add_discount'},
            success: function (data) {
                update_cart(data.items, 'item');
                update_cart_small(data);
                popup({block_id: '#popupOrderByClick', action: 'open'});
                if (data.price_delivery_popup != 0) {
                    $('#min_sum_delivery_popup').html(data.min_sum_delivery)
                    $('#price_delivery_popup').html(data.price_delivery_popup)
                    if ($('#text_delivery_popup').hasClass('hidden')) {
                        $('#text_delivery_popup').removeClass('hidden');
                    }
                } else {
                    if (!$('#text_delivery_popup').hasClass('hidden')) {
                        $('#text_delivery_popup').addClass('hidden');
                    }
                }
            },
            error: function () {

            }
        });
    } else {
//				location.replace($('#cart a').attr('href'));
    }
}).on('click', '.fast_discount', function (e) {
    e.preventDefault();
    $('#fast_order-type', '#fastOrder').val(4);
    $('#fast_order-items', '#fastOrder').val($(this).data('id-item'));
    popup({block_id: '#fastOrder', action: 'open'});
});
function update_cart_small(data) {
    $('.cart_count_int').text(data.count);
    $('.cart_count_string').html(data.count_string);
    $('.cart_sum_string').text(data.sum)
    $('.cart_small_full').text(data.sum_full);
    $('.cart_small_add_bonus').text(data.add_bonus);
    if (typeof data.discount_price != 'undefined') {
        $('.cart_small_discount').html(data.discount_price);
        if ($('.cart_small_discount').closest('div.result').hasClass('hidden')) {
            $('.cart_small_discount').closest('div.result').removeClass('hidden')
        }
    } else {
        if (!$('.cart_small_discount').closest('div.result').hasClass('hidden')) {
            $('.cart_small_discount').closest('div.result').addClass('hidden')
        }
    }
}

function update_cart(items, type) {
    type = typeof type !== 'undefined' ? type : 'item';
    $.each(items, function (id, el) {
        if (typeof el.new != 'undefined') {
            var content = $.parseHTML(el.new);
            $(content).hide();
            $('.mCSB_container', '#cart_items').prepend(content);
            $(content).slideToggle();
        } else if (typeof el.count != 'undefined') {
            var button_del = $('.delGoods[data-id=' + id + '][data-type=' + type + ']', '#cart_items');
            var num_div = $('.num', $(button_del).parents('.cartBlock'));
            var count = $(num_div).data('val');
            count.replace('{count}', el.count)
            $(num_div).html(count.replace('{count}', el.count))
            if (typeof el.price_full != 'undefined') {
                $('.price', $(button_del).parents('.cartBlock')).text(el.price_full);
            }
        }
    })
}
JS
)
?>
