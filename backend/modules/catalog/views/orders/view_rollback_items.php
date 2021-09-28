<?php
/**
 *
 * @var \common\models\Orders $order
 * @var $items \common\models\OrdersItems[]
 * @var $sets \common\models\OrdersSets[]
 * @var $rollback_items \common\models\OrdersRollbackItems[]
 * @var $rollback_sets \common\models\OrdersRollbackSets[]
 */
use common\models\OrdersItems;
use common\models\OrdersRollbackItems;
use common\models\OrdersRollbackSets;
use common\models\OrdersSets;

$rollback_items = OrdersRollbackItems::find()->where(['order_id' => $order->id])->all();
//$rollback_sets = OrdersRollbackSets::find()->where(['order_id' => $order->id])->all();
$items = OrdersItems::find()->with('item')->indexBy('id')->where(['order_id' => $order->id])->all();
//$sets = OrdersSets::find()->with('set')->indexBy('id')->where(['order_id' => $order->id])->all();
/**
 * @var $functions \frontend\components\FunctionComponent
 */
foreach ($items as $key => &$value) {
    $value->populateRelation('item', $order->convert_to_model($value, $value->item));
}
?>
<? if ($rollback_items): ?>
    <div class="row"><strong>Товары</strong></div>
    <div class="table-responsive table-primary row col-xs-6">
        <table class="table table-striped table-hover">
            <colgroup>
                <col width="250px">
                <col width="100px">
                <col width="100px">
            </colgroup>
            <thead>
            <tr>
                <th>Наименование</th>
                <th>Количество <br> (кг/шт)</th>
                <th>Вес</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rollback_items as $rollback_item): ?>
                <? if (isset($items[$rollback_item->item_order_id])): ?>
                    <tr>
                        <td>
                            <?= $items[$rollback_item->item_order_id]->item->name ?>
                        </td>
                        <td><?= $rollback_item->count ?></td>
                        <td><?= $rollback_item->weight ?></td>
                    </tr>
                <? endif ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<? endif ?>
<? /* if ($rollback_sets): ?>
    <div class="row"><strong>Сеты</strong></div>
    <div class="table-responsive table-primary row col-xs-6">
        <table class="table table-striped table-hover">
            <colgroup>
                <col width="250px">
                <col width="50px">
            </colgroup>
            <thead>
            <tr>
                <th>Наименование</th>
                <th>Количество <br> (шт)</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rollback_sets as $rollback_set): ?>
                <? if (isset($sets[$rollback_set->set_order_id])): ?>
                    <tr>
                        <td>
                            <?= $sets[$rollback_set->set_order_id]->set->name ?>
                        </td>
                        <td><?= $rollback_set->count ?></td>
                    </tr>
                <? endif ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<? endif */ ?>
