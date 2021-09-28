<?
/**
 * @var $this yii\web\View
 * @var $order \common\models\Orders
 * @var $users \backend\models\SUser[]
 */
use backend\models\SUser;

$users = SUser::find()->indexBy('id')->all();
$this->registerJs('window.print()', $this::POS_LOAD);
$this->registerCss('@page {
  margin-bottom: 0;
}
')
?>
<div class="container">
    <div class="pull-left" style="width: 100%">
        <?= Yii::$app->settings->get('text_header_print_order') ?>
    </div>
    <h3 class="text-center">Заказ №<?= $order->id ?> от <?= date('d.m.Y', $order->created_at) ?></h3>
    <div class="pull-left" style="width: 100%">
        <? if ($order->id_1c): ?>
            <b>Накладная № <?= $order->id_1c ?></b><br />
        <? endif ?>
        <b>Менеджер:</b> <?= isset($users[$order->manager_id]) ? $users[$order->manager_id]->username : 'Нет менеджера' ?> <br />
        <b>Операционист:</b> <?= isset($users[$order->collector_id]) ? $users[$order->collector_id]->username : 'Нет операциониста' ?> <br />
        <b>Водитель:</b> <?= isset($users[$order->driver_id]) ? $users[$order->driver_id]->username : 'Нет водителя' ?> <br />
    </div>
    <?= $this->render('print/items', ['order' => $order]) ?>
    <div class="pull-left well">
        <h3 style="margin: 0">Информация о получателе</h3>
        <strong>ФИО: </strong> <?= $order->user_name ?> <br>
        <strong>Телефон: </strong> <?= $order->user_phone ?> <br>
        <strong>Способ оплаты: </strong> <?= (isset($order->data_payment[$order->payment])) ? $order->data_payment[$order->payment] : '' ?> <br>
        <strong>Адрес доставки: </strong> <?= $order->user_address ?> <br>
        <strong>Дата и время доставки: </strong>
        <ul style="list-style: none;padding-left: 16px;">
            <li>
                <strong>Дата: </strong> <?=date('d.m.Y',$order->date_delivery)?>
            </li>
            <li>
                <strong>Время: </strong> <?=$order->time_delivery?>
            </li>
        </ul>
        <strong>Комментарий: </strong> <?= $order->user_comments ?> <br>
    </div>
    <div class="clearfix"></div>
    <div class="row" style="padding-top: 40px;">
        <div class="pull-left">
            <strong>Отпустил _________________</strong>
        </div>
        <div class="pull-right">
            <strong>Получил _________________</strong>
        </div>
    </div>
</div>