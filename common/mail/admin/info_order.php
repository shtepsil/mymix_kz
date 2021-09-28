<?php
/**
 * @var $this yii\web\View
 * @var $item Orders
 * @var $user backend\models\SUser
 */

use backend\modules\catalog\models\Orders;

?>
<div>
    Смена статуса пользователем <?=$user->username?>
</div>
<div>
    <p><a href="<?=Yii::$app->urlManager->createAbsoluteUrl(['orders/control', 'id' => $item->id])?>">Посмотреть заказ</a></p>
    <p>Заказ №<?=$item->id?></p>
    <p>Статус: <?=$item->data_status[$item->status]?></p>
    <p>ФИО:<?=$item->user_name?></p>
    <p>Почта:<?=$item->user_mail?></p>
    <p>Телефон:<?=$item->user_phone?></p>
    <p>Адрес:<?=$item->user_address?></p>
    <? if ($item->user_comments): ?>
        <p>Коментарий:<?= $item->user_comments ?></p>
    <? endif ?>
    <p>Сумма заказа:<?=$item->full_price?></p>
</div>
