<?php
/**
 * @var $item \common\models\Items | \common\models\OrdersItems
 * @var $type_handling \common\models\ItemsTypeHandling[]
 * @var string $name
 */
use common\models\OrdersItems;
use yii\helpers\Html;

$discount = 0;
/**
 * @var $functions \frontend\components\FunctionComponent
 */
$functions = Yii::$app->function_system;
if ($item instanceof OrdersItems) {
    $order_item = $item;
    $count = (double)$item->count;
    $price = $item->price;
    $weight = (double)$order_item->weight;
//    $purch_price = $item->purch_price;
//    $select_types = $item->getOrdersItemsHandings()->indexBy('type_handling_id')->all();
    $item = $item->item;
//    if ($item->purch_price != $purch_price) {
//        $purch_price = $item->purch_price;
//    }
//    $type_handling = $item->getItemsTypeHandlings()->indexBy('type_handling_id')->with('typeHandling')->all();
    $item_price = $item->sum_price($count, 'main', $price, $weight);
    if (isset($discounts)&& $discounts) {
        $item_price_discount = $functions->full_item_price($discounts, $item, $count, $weight);
        $discount = ($item_price - $item_price_discount);
        $item_price = $item_price - $discount;
    }
} else {
    $select_types = [];
    $weight = $item->weight;
    $count = 1;
    $price = $item->price;
//    $purch_price = $item->purch_price;
//    $type_handling = $item->getItemsTypeHandlings()->indexBy('type_handling_id')->with('typeHandling')->all();
    $item_price = $item->sum_price($count, 'main');
}
$input_name = $name . "[{$item->id}]";
?>
<tr class="item" id="<?= $name ?>_<?= $item->id ?>">
    <td>
        <?= Html::a($item->name, ['items/control', 'id' => $item->id], ['target' => '_blank']) ?>
        <? // Html::hiddenInput("{$input_name}[purch_price]", $purch_price, [ 'data-purch_price' => $price ])?>
    </td>
    <td>
        <?
        if (Yii::$app->user->can('change_price_item_order')) {
            echo Html::textInput("{$input_name}[price]", $price, [
                'class' => 'form-control price_item',
            ]);
        } else {
            echo $price;
        }
        ?>
    </td>
    <td><? // ($item->measure_price == 0) ? 'кг' : 'шт' ?></td>
    <td>
        <div class="form-inline ">
            <div class="form-group ">
                <?= Html::textInput("{$input_name}[count]", doubleval($count), [
                    'class' => 'form-control',
                    'style' => 'width: 50px;',
                ]) ?>
                <label><? // ($item->measure == 0) ? 'кг' : 'шт' ?>.</label>
            </div>
        </div>
    </td>
    <td>
        <? /* if ($item->measure != $item->measure_price): ?>
            <?= Html::textInput("{$input_name}[weight]", doubleval($weight), [
                'class' => 'form-control',
            ]) ?>
        <? endif; */ ?>
    </td>
    <td class="sum_item_discount"><?= ($discount) ? $discount : '' ?></td>
    <td class="sum_item"><?= $item_price ?></td>
</tr>
