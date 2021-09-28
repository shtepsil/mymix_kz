<?php
/**
 * @var $item Items | OrdersItems
 * @var $type_handling ItemsTypeHandling[]
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
if ($item instanceof OrdersItems) {
    $order_item = $item;
    $count = (double)$item->count;
    $price = $item->price;
    $weight = (double)$order_item->weight;
    $item = $item->item;
    $item_price = $item->sum_price($count);
    if (isset($discounts) && $discounts) {
        $item_price_discount = $functions->full_item_price($discounts, $item, $count, $weight);
        $discount = ($item_price - $item_price_discount);
        $item_price = $item_price - $discount;
    }
} else {
    $weight = $item->weight;
    $count = 1;
    $price = $item->real_price();
    if (isset($isWholesale) && $isWholesale == 1 && $item->wholesale_price) {
        $price = $item->wholesale_price;
    }
    $item_price = $item->sum_price($count);
}
$input_name = $name . "[{$item->id}]";
?>
<tr class="item" id="<?= $name ?>_<?= $item->id ?>">
    <td>
        <?= Html::a($item->name, ['items/control', 'id' => $item->id], ['target' => '_blank']) ?>
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
    <td>
        <div class="form-inline ">
            <div class="form-group ">
                <?= Html::textInput("{$input_name}[count]", doubleval($count), [
                    'class' => 'form-control',
                    'style' => 'width: 50px;',
                ]) ?>
                <label><?= ($item->measure == 0) ? 'кг' : 'шт' ?>.</label>
            </div>
        </div>
    </td>
    <td class="sum_item_discount"><?= ($discount) ? $discount : '' ?></td>
    <td class="sum_item"><?= $item_price ?></td>
    <td class="actions text-center deleted-<?= $name ?>">
        <a href="#" class="btn btn-xs btn-danger" title="Удалить" data-id="<?= $item->id ?>"><i class="fa fa-times fa-inverse"></i></a>
    </td>
</tr>
