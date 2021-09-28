<?php
/**
 * @var $item Items | OrdersItems
 * @var string $name
 */

use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\OrdersItems;
use yii\helpers\Html;

$discount = 0;
/**
 * @var $functions \frontend\components\FunctionComponent
 */
$functions = Yii::$app->function_system;
$order_item = $item;
$count = (double)$item->count;
$price = $item->price;
$weight = (double)$order_item->weight;

$item = $item->item;
/*
$item_price = $item->real_sum_price($count);
if (isset($discounts)) {
    $item_price_discount = $functions->full_item_price($discounts, $item, $count, $weight);
   $discount = ($item_price - $item_price_discount);
    $item_price = $item_price - $discount;
}
*/

$price_origin = Items::find()->where(["id" => $item->id])->one();
$item_discount = $price_origin->price - $price;
?>
<tr>
    <td style="padding:4px; border:1px solid gray;">
        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['/site/item', 'id' => $item->id]) ?>">
            <?= $item->name ?>
        </a>
    </td>
    <td style="padding:4px; border:1px solid gray;text-align: center">
        <?= doubleval($count) ?>
    </td>
    <td style="padding:4px; border:1px solid gray;text-align: center">'шт' </td>
    <td style="padding:4px; border:1px solid gray;text-align: center"><?= number_format($price_origin->price, 0, '.', ' ') ?></td>
	<td style="padding:4px; border:1px solid gray;text-align: center"><?= ($item_discount) ? ($item_discount*$count)   : '' ?></td>
    <td style="padding:4px; border:1px solid gray;text-align: center"><?= number_format(($price*$count), 0, '.', ' ') ?></td>
</tr>




