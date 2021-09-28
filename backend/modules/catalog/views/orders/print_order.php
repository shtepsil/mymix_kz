<?php
/**
 * @var \yii\web\View $this
 * @var $context \shadow\widgets\AdminForm
 * @var $order \common\models\Orders
 * @var $items_order \common\models\OrdersItems[]
 * @var $item \common\models\Items
 * @var string $name
 */
use common\models\OrdersItems;

$items_order = OrdersItems::find()->with('item')->where(['order_id' => $order->id])->all();
?>
<div class="table-responsive table-primary row">
    <table class="table table-striped table-hover">
        <colgroup>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
        </colgroup>
        <thead>
        <tr>
            <th>Код</th>
            <th>Название</th>
            <th>Цена за ед.</th>
            <th>Количество</th>
            <th>Ед. измер.</th>
            <th>Сумма</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items_order as $item_order): ?>
            <?
            $item = $item_order->item;
            $price = $item_order->price;
            $count = $item_order->count;
            ?>
            <tr class="item">
                <th><?= $item->id ?></th>
                <td><?= $item->name ?></td>
                <td><?= $price ?></td>
                <td><?= $count ?></td>
                <td><?= $item->measure0->short ?></td>
                <td class="sum_item"><?= round($price * $count) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="row">
    <div class="pull-right">
        <div class="row">
            <span>Сумма заказа:</span>
                <span class="text-right">
                    <span id="order_sum"><?= $order->full_price ?></span> тенге.
                </span>
        </div>
        <? if ($order->price_delivery>0): ?>
            <div class="row">
                <div>Стоимость доставки:</div>
                <div>
                    <?= $order->price_delivery ?>
                </div>
            </div>
        <? endif ?>
        <? if ($order->bonus_use): ?>
            <div class="row">
                <span>Использованно бонусов:</span>
                <span class="text-right">
                    <span id="bonus_price"><?= $order->bonus_use ?></span>
                </span>
            </div>
        <? endif ?>
        <?
        $sum_real = $order->full_price;
        if($order->price_delivery>0){
            $sum_real += $order->price_delivery;
        }
        ?>
        <div class="row">
            <span>К оплате:</span>
                <span class="text-right">

                    <span id="full_price"><?= ($sum_real - $order->bonus_use) ?></span> тенге.
                </span>
        </div>
    </div>
</div>