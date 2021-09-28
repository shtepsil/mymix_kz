<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 * @var $order Orders
 * @var $old_orders Orders[]
 */

use backend\modules\catalog\models\Orders;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
?>
<div class="breadcrumbsWrapper padSpace">
    <?= $this->render('//blocks/breadcrumbs') ?>
</div>
<div class="Cabinet padSpace">
    <div class="gTitle_line">
        <div class="gTitle">Заказ №<?= $order->id ?></div>
        <div class="right_line">
            <div class="delivered">Оформлен <?= Yii::$app->formatter->asDate($order->created_at, 'd MMMM Y'); ?>  г.</div>
            <div class="dlc_links">
                <a href="<?= Url::to(['user/replay-order','id'=>$order->id]) ?>" class="reorder"><span>Перезаказать</span></a>
                <? if (false): ?>
                    <a href="#" class="print"><span>Распечатать</span></a>
                <? endif ?>
            </div>
        </div>
    </div>
    <table class="adpTable order">
        <thead>
        <tr>
            <td class="zN">№</td>
            <td class="zGoods">Товар</td>
            <td class="zNum">Количество</td>
            <td class="zPrice">Цена</td>
            <td class="zRes">Итог</td>
        </tr>
        </thead>
        <tbody>
        <?
        $sum = 0;
        $i = 0;
        ?>
        <?php foreach ($order->ordersItems as $item_order): ?>
            <?php
            $count = (double)$item_order->count;
            $type_handling = [];
//            $type_handling[$item_order->item_id] = $item_order->getOrdersItemsHandings()->select('type_handling_id')->column();
            $no_item = false;
            $item = $item_order->item;
            $item_sum = $item->sum_price($count);
            $price_item = number_format($item_order->price, 0, '', ' ');
            //$sum += $item_sum;
			$sum += ($item_order->price*$count);
            ?>
            <tr id="items-<?= $item->id ?>">
                <td class="zN" data-title="№"><?= ++$i ?></td>
                <td class="zGoods" data-title="Товар">
                    <a href="<?= $item->url() ?>"><?= $item->name ?></a>
                    <br />
                    <? if ($item->vendor_code): ?>
                        <span>арт. <?= $item->vendor_code ?></span>
                    <? endif ?>
                </td>
                <td class="zNum" data-title="Количество">
                    <?= $count ?> шт
                    <? if (false&&$item->itemsTypeHandlings): ?>
                        <?php
                        $handing_string = '';
                        foreach ($item->itemsTypeHandlings as $item_handling) {
                            if (!$item_handling->typeHandling->isVisible || !isset($handling[$item_handling->type_handling_id])) {
                                continue;
                            }
                            if ($handing_string) {
                                $handing_string .= '<br>';
                            }
                            $handing_string .= '+' . $item_handling->typeHandling->name;
                        }
                        if ($handing_string) {
                            echo Html::tag('span', $handing_string);
                        }
                        ?>
                    <? endif ?>
                </td>
                <td class="zPrice" data-title="Цена"><b><?= $price_item ?> т.</b></td>
                <td class="zRes" data-title="Итог">
                    <!--<b><?= number_format($item_sum, 0, '', ' ') ?> т.</b>-->
					<b><?=number_format(($item_order->price * $count), 0, '', ' ')?> т.</b>
                </td>
            </tr>
        <?php endforeach; ?>
        <? if ($order->price_delivery): ?>
            <tr class="result">
                <td class="zN"></td>
                <td class="zGoods" colspan="3">Доставка</td>
                <td class="zRes"><b><?= ($order->price_delivery) ? ($order->price_delivery . ' т.') : '' ?></b></td>
            </tr>
        <? endif ?>
        <? if ($order->bonus_use): ?>
            <tr class="result">
                <td class="zN"></td>
                <td class="zGoods" colspan="3">Использовано бонусов</td>
                <td class="zRes"><b><?= number_format($order->bonus_use, 0, '', ' ') ?> т.</b></td>
            </tr>
        <? endif ?>
        <? if (trim($order->discount)): ?>
            <tr class="result">
                <td class="zN"></td>
                <td class="zGoods" colspan="3">Скидка</td>
                <td class="zRes"><b><?= $order->discount ?></b></td>
            </tr>
        <? endif ?>
        <tr class="result">
            <td class="zN"></td>
            <td class="zGoods" colspan="3">Итого к оплате</td>
            <td class="zRes"><b><?= number_format((($sum + $order->price_delivery) - $order->discount($sum)) - $order->bonus_use , 0, '', ' ') ?> т.</b></td>
        </tr>
        <? if ($order->bonus_add > 0 && $order->isWholesale == 0): ?>
            <tr class="result">
                <td class="zN"></td>
                <td class="zGoods" colspan="3">Начислено бонусов</td>
                <td class="zRes"><b><?= $order->bonus_add ?></b></td>
            </tr>
        <? endif ?>
        </tbody>
    </table>
    <div class="additionalInformation">
        <div class="block_inf">
            <div class="title">Адрес доставки</div>
            <div class="text">
                <?=$order->user_name?> <br />
                <?=$order->user_address?>  <br />
                T: <?=$order->user_phone?> <br />
            </div>
        </div>
        <div class="block_inf">
            <div class="title">Метод оплаты</div>
            <div class="text">
                <?=$order->data_payment[$order->payment]?>
            </div>
        </div>
    </div>
</div>