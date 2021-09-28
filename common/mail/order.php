<?php
/**
 * @var $this yii\web\View
 * @var $order Orders
 */

use backend\modules\catalog\models\Orders;

/**
 * @var $settings \shadow\SSettings
 */
$settings = Yii::$app->settings;
?>
<div>
    <h4>Детализация заказа</h4>
    <? if ($settings->get('mail_order_header')): ?>
        <div><?= str_replace('{user_name}', $order->user_name, $settings->get('mail_order_header')) ?></div>
    <? endif ?>
    <ul>
        <li>Заказ #<?= $order->id; ?></li>
        <li>Имя: <?= $order->user_name; ?></li>
        <? if ($order->user_mail): ?>
            <li>Email: <?= $order->user_mail; ?></li>
        <? endif ?>
        <li>Телефон: <?= $order->user_phone; ?></li>
        <li>Адрес доставки: <?= $order->user_address ?></li>
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
    <?= $this->render('blocks/items', ['order' => $order]) ?>
    <? if ($settings->get('mail_order_footer')): ?>
        <div><?= str_replace('{user_name}', $order->user_name, $settings->get('mail_order_footer')) ?></div>
    <? endif ?>
</div>
