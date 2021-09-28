<?php
/**
 * @var $item \common\models\Sets | \common\models\OrdersSets
 * @var $type_handling \common\models\ItemsTypeHandling[]
 * @var string $name
 */
use common\models\OrdersItems;
use common\models\OrdersSets;
use yii\helpers\Html;

if ($item instanceof OrdersSets) {
    $count = $item->count;
    $price = $item->price;
    $purch_price = $item->purch_price;
    $item = $item->set;
} else {
    $select_types = [];
    $count = 1;
    $price = $item->real_price();
    $purch_price = $item->real_purch_price();
}
$input_name = $name . "[{$item->id}]"
?>
<tr class="item">
    <td>
        <?= Html::a($item->name, ['sets/control', 'id' => $item->id], ['target' => '_blank']) ?>
        <?= Html::hiddenInput("{$input_name}[purch_price]", $purch_price, [
            'data-purch_price' => $price
        ]) ?>
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
        <?= Html::textInput("{$input_name}[count]", number_format($count, 1, '.', ''), [
            'class' => 'form-control',
            'data-price' => $price
        ]) ?>
    </td>
    <td class="sum_item"><?= round($price * $count) ?></td>
</tr>
