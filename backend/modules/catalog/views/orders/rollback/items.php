<?php
/**
 *
 * @var \yii\web\View $this
 * @var $context \shadow\widgets\AdminForm
 * @var $order \common\models\Orders
 * @var $items \common\models\OrdersItems[]
 * @var $sets \common\models\OrdersSets[]
 * @var $form \shadow\widgets\AdminActiveForm
 * @var string $name
 */
use common\models\OrdersItems;
use common\models\OrdersSets;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$context = $this->context;
$items = $sets = $discount = [];
if (!$order->isNewRecord) {
    $items = OrdersItems::find()->with('item')->where(['order_id' => $order->id])->all();
//    $sets = OrdersSets::find()->with('set')->where(['order_id' => $order->id])->all();
    /**
     * @var $functions \frontend\components\FunctionComponent
     */
    $functions = Yii::$app->function_system;
    $db_items = $sessions_items = [];
    foreach ($items as $key => &$value) {
        $value->populateRelation('item', $order->convert_to_model($value, $value->item));
        $db_items[$value->item->id] = $value->item;
        $sessions_items[$value->item->id] = $value->count;
    }
    if (!trim($order->discount)) {
        if ($order->isWholesale == 0) {
            $discount = $functions->discount_sale_items($db_items, $sessions_items);
        } else {
            $discount = [];
        }
    } else {
        $discount = [];
    }
} else {
    $order->price_delivery = 0;
}
$name = 'ordersItems';
$name_sets = 'ordersSets';
$in_id = $in_id_sets = [];
?>
<?= Html::activeHiddenInput($order, 'isWholesale') ?>
    <div class="row"><strong>Товары</strong></div>
    <div class="table-responsive table-primary row">
        <table class="table table-striped table-hover">
            <colgroup>
                <col width="30%">
                <col width="100px">
                <col width="40px">
                <col width="60px">
                <col width="85px">
                <col width="100px">
                <col width="100px">
            </colgroup>
            <thead>
            <tr>
                <th>Название</th>
                <th>Цена<br> за ед.</th>
                <th>Ед.<br> расчёта</th>
                <th>Кол-во</th>
                <th>Вес</th>
                <th>Скидка</th>
                <th>Стоимость</th>
            </tr>
            </thead>
            <tbody id="items-<?= $name ?>">
            <?php foreach ($items as $item): ?>
                <?= $this->render('item', ['item' => $item, 'name' => $name, 'discounts' => $discount]) ?>
                <?php
                $in_id[$item->item->id] = $item->item->id;
                ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="row"><strong>Сеты</strong></div>
    <div class="table-responsive table-primary row">
        <table class="table table-striped table-hover">
            <colgroup>
                <col>
                <col>
                <col>
                <col width="100px">
            </colgroup>
            <thead>
            <tr>
                <th>Название</th>
                <th>Цена за ед.</th>
                <th>Количество</th>
                <th>Стоимость</th>
            </tr>
            </thead>
            <tbody id="items-<?= $name_sets ?>">
            <?php
            /*
            foreach ($sets as $set): ?>
                <?= $this->render('set', ['item' => $set, 'name' => $name_sets]) ?>
                <?php
                $in_id_sets[$set->set->id] = $set->set->id;
                ?>
            <?php endforeach;
            */
            ?>
            </tbody>
        </table>
    </div>
<?= Html::activeHiddenInput($order, 'price_delivery', ['class' => 'form-control', 'id' => 'price_delivery']) ?>
<?= Html::activeHiddenInput($order, 'discount', ['class' => 'form-control', 'id' => 'price_discount']) ?>

    <div class="row">
        <div class="pull-left">
            <div class="row">
                <span>Сумма заказа:</span>
                <span class="text-right">
                    <span id="order_sum"><?= $order->full_price ?></span> тенге.
                </span>
            </div>
            <div class="row">
                <div>Стоимость доставки: <?= $order->price_delivery ?></div>
            </div>
            <div class="row">
                <div>Скидка: <?=$order->discount?></div>
            </div>
            <div class="row">
                <span>Использованно бонусов:</span>
                <span class="text-right">
                    <span id="bonus_price"><?= $order->bonus_use ?></span>
                </span>
            </div>
            <div class="row">
                <span>К оплате:</span>
                <span class="text-right">
                    <span id="full_price"><?= (($order->full_price + $order->price_delivery) - $order->discount($order->full_price)) - $order->bonus_use ?></span> тенге.
                </span>
            </div>
            <? if (Yii::$app->user->can('manager')): ?>
                <div class="row">
                    <span>Бонус менеджеру:</span>
                <span class="text-right">
                    <span id="full_bonus_manager"><?= $order->bonus_manager ?></span>
                </span>
                </div>
            <? endif ?>
        </div>
    </div>
    <div class="padding-sm clearfix"></div>
<?php
$url_change_item = Url::to(['orders/change-item']);
$id_order = Json::encode($order->id);
$this->registerCss(<<<CSS
.tt-dropdown-menu {
  max-height: 250px;
  overflow-y: auto;
  overflow-x: hidden;
}
CSS
);
$this->registerJs(<<<JS
var id_order={$id_order};
JS
    , $this::POS_HEAD);
$this->registerJs(<<<JS
$('#items-{$name}').on('click', '.deleted-{$name}>a', function (e) {
    e.preventDefault();
    $(this).parents('tr').remove();
    update_price();
}).on('change', 'input', function () {
    update_price();
});
$('#items-{$name_sets}').on('click', '.deleted-{$name_sets}>a', function (e) {
    e.preventDefault();
    $(this).parents('tr').remove();
    update_price();
}).on('change', 'input', function () {
    update_price();
});
function update_price() {
    var sum = 0;
    var data_request = $('input', '#items-{$name},#items-{$name_sets}').serializeArray();
    var discount = $('#price_discount').val();
    var isWholesale = 0;
    if($('#orders-iswholesale').prop('checked')){
        isWholesale = 1;
    }
    var price_delivery = $('#price_delivery').val();
    data_request.push({name: 'id', value: id_order})
    data_request.push({name: 'discount', value: discount})
    data_request.push({name: 'delivery', value: price_delivery})
    data_request.push({name: 'isWholesale', value: isWholesale})
    $.ajax({
        url: "{$url_change_item}",
        type: 'POST',
        dataType: 'JSON',
        data: data_request,
        success: function (data) {
            edit_basket(data.items, '{$name}');
            edit_basket(data.sets, '{$name_sets}');
            $('#order_sum').text(data.sum);
            $('#full_price').text(data.full_price);
            $('#full_bonus_manager').text(data.full_bonus_manager);
        },
        error: function () {
            $.growl.error({title: 'Ошибка', message: 'Произошла ошибка на стороне сервера', duration: 5000});
        }
    });
//    $('input[data-price]', '#items-{$name}').each(function (i, el) {
//        var item_price = Math.round($(el).val() * $(el).data('price'));
//        $('.sum_item', $(el).parents('tr')).text(item_price)
//        sum = Math.round(sum + item_price);
//    });
//    $('input[data-price]', '#items-{$name_sets}').each(function (i, el) {
//        var item_price = Math.round($(el).val() * $(el).data('price'));
//        $('.sum_item', $(el).parents('tr')).text(item_price)
//        sum = Math.round(sum + item_price);
//    });
//    $('#order_sum').text(sum);
//    sum = parseInt($('#price_delivery').val()) + sum
//    sum = sum - (parseInt($('#bonus_price').text()))
//    $('#full_price').text(sum);
}
function edit_basket(items, type) {
    $.each(items, function (id, el) {
        if (typeof el.price != 'undefined') {
            $('.sum_item', '#' + type + '_' + id).html(el.price);
        }
        if (typeof el.discount != 'undefined') {
            $('.sum_item_discount', '#' + type + '_' + id).html(el.discount);
        }
    })
}
JS
);
?>