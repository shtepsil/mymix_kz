<?php
/**
 * @var $this yii\web\View
 * @var $order Orders
 */

use backend\modules\catalog\models\Orders;

?>
<div>
    <h4>Детализация заказа</h4>
    <ul>
        <li>Заказ #<?= $order->id; ?></li>
        <li>Имя: <?= $order->user_name; ?></li>
        <? if ($order->user_mail): ?>
            <li>Email: <?= $order->user_mail; ?></li>
        <? endif ?>
        <li>Телефон: <?= $order->user_phone; ?></li>
        <li>Адрес доставки: <?= $order->user_address ?></li>
        <? if ($order->time_delivery): ?>
            <li>Время доставки: <?= $order->time_delivery ?></li>
        <? endif ?>
        <? if ($delivery): ?>
            <li>Способ доставки: <?= $delivery ?></li>
        <? endif ?>
        <? if ($name_pickup): ?>
            <li>Пункт самовывоза: <?= $name_pickup ?></li>
        <? endif ?>
        <? if ($days > 0): ?>
            <li>Примерное количество дней: <?= $days ?></li>
        <? endif ?>
        <? if ($order->payment && isset($order->data_payment[$order->payment])): ?>
            <li>Способ оплаты: <?= $order->data_payment[$order->payment] ?></li>
        <? endif ?>
        <? if (!empty($order->user_comments)) : ?>
            <li>Комментарий: <?= $order->user_comments; ?></li>
        <? endif; ?>
    </ul>
    <h4>Детализация заказа</h4>
    <?= $this->render('@common/mail/blocks/items', ['order' => $order]) ?>
</div>
