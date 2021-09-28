<?php
/**
 * @var $item Items | OrdersItems
 * @var $select_types \common\models\ItemsTypeHandling[]
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
$purch_price = $item->purch_price;
//$select_types = $item->getOrdersItemsHandings()->with('typeHandling')->indexBy('type_handling_id')->all();
$item = $item->item;
//if ($item->purch_price != $purch_price) {
//    $purch_price = $item->purch_price;
//}

// $item_price = $item->sum_price($count);
// if (isset($discounts)) {
    // $item_price_discount = $functions->full_item_price($discounts, $item, $count, $weight);
    // $discount = ($item_price - $item_price_discount);
    // $item_price = $item_price - $discount;
// }
  
 $price_origin = Items::find()->where(["id" => $item->id])->one();
 $item_discount = $price_origin->price - $price;
?>
<tr>
    <td><?=$item->vendor_code?></td>
    <td>
        <?= $item->name ?>
    </td>
    <td style="text-align: center">
        <?= doubleval($count) ?>
    </td>
    <td style="text-align: center">шт</td>
    <td style="text-align: center"><?= number_format($price_origin->price, 0, '.', ' ') ?></td>
    <td style="text-align: center"><?= ($item_discount) ? ($item_discount*$count)   : '' ?></td>
    <td style="text-align: right"><?= number_format(($price*$count), 0, '.', ' ') ?></td>
</tr>
