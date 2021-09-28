<?php
/**
 *
 * @var \yii\web\View $this
 * @var $context \shadow\widgets\AdminForm
 * @var $order Orders
 * @var $items OrdersItems[]
 * @var $sets OrdersSets[]
 * @var $form \shadow\widgets\AdminActiveForm
 * @var string $name
 */


use backend\modules\catalog\models\Orders;
use backend\modules\catalog\models\OrdersItems;

$context = $this->context;
$items = $sets = $discount = [];
$items = OrdersItems::find()->with('item')->where(['order_id' => $order->id])->all();
//$sets = OrdersSets::find()->with('set')->where(['order_id' => $order->id])->all();
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
?>
<table class="table" style="margin-top: 20px">
    <colgroup>
        <col width="100px">
        <col width="250px">
        <col width="100px">
        <col width="80px">
        <col width="85px">
        <col width="100px">
        <col width="100px">
    </colgroup>
    <thead>
    <tr>
        <th>Артикул</th>
        <th>Название</th>
        <th style="text-align: center">Кол-во</th>
        <th style="text-align: center">Ед. изм.</th>
        <th style="text-align: center">Цена</th>
        <th style="text-align: center">Скидка</th>
        <th style="text-align: right">Сумма</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <?= $this->render('item', ['item' => $item, 'discounts' => $discount]) ?>
    <?php endforeach; ?>
    <?php foreach ($sets as $item): ?>
        <?= $this->render('set', ['item' => $item, 'name' => $name_sets]) ?>
    <?php endforeach; ?>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align: center"><strong>Сумма</strong></td>
        <td style="text-align: right"><?= number_format($order->full_price, 0, '.', '') ?></td>
    </tr>
    <? if ($order->price_delivery): ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: center"><strong>Доставка</strong></td>
            <td style="text-align: right"><strong><?= number_format($order->price_delivery, 0, '.', '') ?></strong></td>
        </tr>
    <? endif ?>
    <? if ($order->bonus_use > 0): ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: center"><strong>Использовано бонусов</strong></td>
            <td style="text-align: right"><strong><?= number_format($order->bonus_use, 0, '.', '') ?></strong></td>
        </tr>
    <? endif ?>
    <? if ($order->discount): ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: center"><strong>Скидка</strong></td>
            <td style="text-align: right"><strong><?= (is_numeric($order->discount) ? (number_format($order->discount, 0, '.', '')) : $order->discount) ?></strong></td>
        </tr>
    <? endif ?>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align: center"><strong>ИТОГО</strong></td>
        <td style="text-align: right"><strong><?= number_format((($order->full_price + $order->price_delivery) - $order->discount($order->full_price)) - $order->bonus_use, 0, '.', ''); ?></strong>
        </td>
    </tr>
    </tbody>
</table>
