<?php

/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 * @var $orders Orders[]
 * @var $old_orders Orders[]
 */

use backend\modules\catalog\models\Orders;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
/**
 * @var $orders Orders[]
 */
$orders = Orders::find()
    ->joinWith([
        'ordersItems',
    ],false)
    ->andWhere(' `orders_items`.id is NOT NULL')
    ->andWhere(['user_id' => $user->id])
    ->limit(20)
    ->orderBy(['created_at' => SORT_DESC])
    ->all();
?>
<div class="breadcrumbsWrapper padSpace">
    <?= $this->render('//blocks/breadcrumbs') ?>
</div>
<div class="Cabinet padSpace">

    <div class="gTitle">Мои заказы</div>

    <table class="adpTable orders">
        <thead>
        <tr>
            <td class="zNum">№ Заказа</td>
            <td class="zDate">Дата</td>
            <td class="zSumm">Сумма</td>
            <td class="zStatus">Статус заказа</td>
            <td class="zRez"></td>
        </tr>
        </thead>
        <tbody>
        <? foreach($orders as $order): ?>
            <?
            $isSuccess = in_array($order->status, [5, 7]);
            $isProcess = false;
            $status = 'Отменён';
            if(!$isSuccess){
                $isProcess = in_array($order->status, [0, 1,2,3,4]);
                if($isProcess){
                    $status = 'Формируется';
                }
            }else{
                $status = 'Доставлен';
            }

            $status = $order->data_status[$order->status];
            ?>
            <tr class="<?=($isSuccess||$isProcess)?'success':'rejected'?>">
                <td class="zNum" data-title="№ Заказа"><a href="<?= Url::to(['user/orders','id'=>$order->id]) ?>"><?=$order->id?></a></td>
                <td class="zDate" data-title="Дата"><?= Yii::$app->formatter->asDate($order->created_at, 'd MMMM Y '); ?></td>
                <td class="zSumm" data-title="Сумма"><b><?= number_format($order->full_price, 0, '', ' ')?> T</b></td>
                <td class="zStatus" data-title="Статус заказа"><div class="bgstatus"><?=$status?></div></td>
                <td class="zRez" data-title=""><a href="<?= Url::to(['user/replay-order','id'=>$order->id]) ?>">Перезаказать</a></td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>

</div>