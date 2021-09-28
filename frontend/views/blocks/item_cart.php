<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $item
 * @var $count
 */

use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Sets;
use yii\helpers\Url;

$context = $this->context;
?>

<? if ($item instanceof Items): ?>
    <?
    /**
     * @var $item Items
     */
    $string_measure = '{count}';
    $type_handling = Yii::$app->session->get('type_handling', []);
    $string_measure = '{count} шт.';

    ?>
    <div class="cartBlock" data-item_id="<?= $item->id ?>">
        <div class="delGoods" data-id="<?= $item->id ?>" data-type="item"></div>
        <div class="image" style="background-image: url(<?= $item->img() ?>);"></div>
        <div class="description">
            <div class="name"><?= $item->name ?></div>
            <? if ($item->body_small): ?>
                <div class="minidesc"><?= $item->body_small ?></div>
            <? endif ?>
            <? if ($item->vendor_code): ?>
                <div class="article">Артикул: <?= $item->vendor_code ?></div>
            <? endif ?>
            <div class="num" data-val="<?= $string_measure ?>"><?= str_replace('{count}', $count, $string_measure) ?></div>
            <div class="price"><?= number_format($item->sum_price($count), 0, '', ' ') ?> 〒</div>
        </div>
        <div class="clear"></div>
    </div>
<? else: ?>
    <?
    /**
     * @var $item Sets
     */
    ?>
    <div class="cartBlock">
        <div class="delGoods" data-id="<?= $item->id ?>" data-type="set"></div>
        <div class="image" style="background-image: url(<?= $item->img ?>);"></div>
        <div class="description">
            <div class="name"><?= $item->name ?></div>
            <div class="num" data-val="{count} шт."><?= $count ?> шт.</div>
            <div class="price"><?= number_format(round($item->real_price() * $count), 0, '', ' ') ?> 〒</div>
        </div>
    </div>
<? endif; ?>
