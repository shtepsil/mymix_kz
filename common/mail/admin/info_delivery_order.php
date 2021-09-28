<?php
/**
 * @var $this yii\web\View
 * @var $orders Orders[]
 * @var $user backend\models\SUser
 */

use backend\modules\catalog\models\Orders;

?>
<div>
    Доброе Утро! На сегодня у Вас запланирована доставка на <?=Yii::t('main','count_orders', ['n' => count($orders)])?>
</div>
<div>
    <?php foreach($orders as $order): ?>
        <p>
            <a href="<?= Yii::$app->urlManagerBackEnd->createAbsoluteUrl(['orders/control', 'id' => $order->id]) ?>">
                Заказ #<?= $order->id ?>
            </a>
        </p>
    <?php endforeach; ?>
</div>
