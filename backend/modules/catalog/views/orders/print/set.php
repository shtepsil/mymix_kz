<?php
/**
 * @var $item \common\models\Sets | \common\models\OrdersSets
 * @var $type_handling \common\models\ItemsTypeHandling[]
 * @var string $name
 */
use common\models\OrdersItems;
use common\models\OrdersSets;
use yii\helpers\Html;

$count = $item->count;
$price = $item->price;
$purch_price = $item->purch_price;
$item = $item->set;
$discount = '';
?>
<tr class="item">
    <td></td>
    <td>
        <?= $item->name ?>
    </td>
    <td style="text-align: center">
        <?= doubleval($count) ?>
    </td>
    <td style="text-align: center">шт</td>
    <td style="text-align: center"><?= number_format($price, 0, '.', ' ') ?></td>
    <td style="text-align: center"><?= ($discount) ? $discount : '' ?></td>
    <td style="text-align: right"><?= number_format(round($price * $count), 0, '.', ' ') ?></td>
</tr>
