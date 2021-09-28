<?php
/**
 *
 * @var \yii\web\View $this
 * @var $context \shadow\widgets\AdminForm
 * @var $user \common\models\User
 * @var $orders \common\models\Orders[]
 */

use backend\modules\catalog\models\Orders;
use yii\helpers\Html;

if ($user->isNewRecord) {
    $orders = [];
} else {
    $orders = Orders::find()->andWhere(['user_id' => $user->id])->orderBy(['created_at' => SORT_DESC])->all();
}
?>
<div class="col-md-12 table-primary">
    <table class="table table-striped table-hover">
        <colgroup>
            <col width="150px">
            <col width="150px">

            <col width="250px">
            <col>
            <col width="100px">
            <col width="100px">
            <col>
        </colgroup>
        <thead>
        <tr>
            <th>Заказ</th>
            <th>Телефон</th>
            <th>Статус</th>
            <th>Сумма</th>
            <th>Дата создания</th>
            <th>Дата доставки</th>
            <th>Адрес доставки</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>
                    <?= Html::a('Заказ №' . $order->id, ['orders/control', 'id' => $order->id], ['target' => '_blank']) ?>
                </td>
                <td><?= $order->getRow('user_phone') ?></td>
                <td><?= $order->getRow('status') ?></td>
                <td><?= $order->getRow('full_price') ?></td>
                <td><?= $order->getRow('created_at') ?></td>
                <td><?= $order->getRow('date_delivery') ?></td>
                <td><?= $order->getRow('user_address') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>