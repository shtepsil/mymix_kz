<?php
/**
 *
 * @var \yii\web\View $this
 * @var               $context \shadow\widgets\AdminForm
 * @var               $order   Orders
 * @var               $items   OrdersItems[]
 * @var               $form    \shadow\widgets\AdminActiveForm
 * @var string        $name
 */

use backend\modules\catalog\models\Orders;
use backend\modules\catalog\models\OrdersItems;

$context = $this->context;
$items   = $sets = $discount = [];
$items   = OrdersItems::find()->with('item')->where(['order_id' => $order->id])->all();
/**
 * @var $settings \shadow\SSettings
 */
$settings = Yii::$app->settings;
/**
 * @var $functions \frontend\components\FunctionComponent
 */
$functions = Yii::$app->function_system;
$db_items  = $sessions_items = [];
foreach ($items as $key => &$value) {
    $value->populateRelation('item', $order->convert_to_model($value, $value->item));
    $db_items[$value->item->id]       = $value->item;
    $sessions_items[$value->item->id] = $value->count;
}
$isWeight = false;
if (!trim($order->discount)) {
    if ($order->isWholesale == 0) {
        $discount = $functions->discount_sale_items($db_items, $sessions_items);
    } else {
        $discount = [];
    }
} else {
    $discount = [];
}
$sum = $order->full_price;
?>
<table class="table" style="border-collapse: collapse; border:1px solid gray; color: rgba(0, 0, 0, 0.73); font-size: 14px; font-family: sans-serif;">
    <colgroup>
        <col width="250px">
        <col width="100px">
        <col width="80px">
        <col width="85px">
        <col width="100px">
        <col width="100px">
    </colgroup>
    <thead>
    <tr>
        <th style="padding:4px; border:1px solid gray;">Название</th>
        <th style="padding:4px; border:1px solid gray;">Кол-во</th>
        <th style="padding:4px; border:1px solid gray;">Ед. изм.</th>
        <th style="padding:4px; border:1px solid gray;">Цена</th>
        <th style="padding:4px; border:1px solid gray;">Скидка</th>
        <th style="padding:4px; border:1px solid gray;">Сумма</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <?= $this->render('item', ['item' => $item, 'discounts' => $discount]) ?>
    <?php endforeach; ?>
    </tbody>
</table>
<h3>Сумма: <?= number_format($order->full_price, 0, '.', '') ?> тг.</h3>
<? if ($order->price_delivery): ?>
    <h3>Доставка: <?= number_format($order->price_delivery, 0, '.', '') ?> тг.</h3>
<? elseif ($order->city_id && ($delivery = Yii::$app->function_system->delivery_price($sum, $order->city_id))): ?>
    <h3>Доставка: <?= $delivery ?></h3>
<? endif; ?>
<? if ($order->bonus_use > 0) : ?>
    <h3>Использовано бонусов: <?= $order->bonus_use; ?></h3>
<? endif; ?>
<? if ($order->discount) : ?>
    <h3>Скидка: <?= (is_numeric($order->discount) ? (number_format($order->discount, 0, '.', '') . 'тг.') : $order->discount) ?></h3>
<? endif; ?>
<h2>ИТОГО: <?= number_format((($sum + $order->price_delivery) - $order->discount($order->full_price)) - $order->bonus_use, 0, '.', ''); ?> тг.</h2>
<? if ($order->bonus_add > 0 && $order->isWholesale == 0) : ?>
    <h3>Будет начислено бонусов: <?= $order->bonus_add; ?></h3>
<? endif; ?>
<? if ($isWeight) : ?>
    <div><?= $settings->get('mail_order_weight') ?></div>
<? endif; ?>
