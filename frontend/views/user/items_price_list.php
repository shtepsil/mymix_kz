<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 * @var $items \common\models\Items[]
 */

$context = $this->context;
$user = $context->user;
?>
<? foreach($items as $item): ?>
    <tr>
        <td class="zArticle" data-title="Артикул"><?=$item->article?></td>
        <td class="zName" data-title="Название">
            <?=$item->name?>
        </td>
        <td class="zNum" data-title="Кол-во">
            <input type="text" value="1" data-measure="<?=$item->measure?>"  /> <?=($item->measure?'шт':'кг')?>
        </td>
        <td class="zPrice" data-title="Цена"><b><?=number_format($item->wholesale_price, 0, '', ' ')?> т./<?=($item->measure_price?'шт':'кг')?></b><s><?=number_format($item->real_price(), 0, '', ' ')?></s></td>
        <td class="zInCart">
            <button class="btn_Form blue addCart" data-id="<?=$item->id?>" data-count="1">В корзину</button>
        </td>
    </tr>
<? endforeach; ?>
