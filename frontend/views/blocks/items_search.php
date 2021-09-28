<?php
/**
 * @var $context \frontend\controllers\SiteController
 * @var $this \yii\web\View
 * @var $content string
 * @var $items backend\modules\catalog\models\Items[]
 */
 use yii\helpers\Html;
$context = $this->context;

?>
<? foreach($items as $item): ?>
    <a class="goods__block__mini" href="<?= $item->url() ?>">

	   <?= Html::img($item->img(), ['style' => 'width:50px;']) ?>
        <div class="__name text_search"><?=$item->name?></div>
        <div class="__price">
            <? $item->real_price() ?>
            <? if ($item->price): ?>
                <? if ($item->old_price): ?>
                    <div class="__old text_search text_search_red"><?= Yii::$app->formatter->asDecimal($item->old_price, 0) ?>
                        <i class="tenge">b</i>
                    </div>
                <? endif ?>
                <div class="__new" style="color:black; font-size:13px">
                    <?php if ($item->isPriceFrom) echo 'от ';?>
                    <?= Yii::$app->formatter->asDecimal($item->price) ?>
                    <i class="tenge">b</i>
                </div>
            <? else: ?>
                <div class="__new">Цена по запросу</div>
            <? endif; ?>
        </div>
    </a><hr>
<? endforeach; ?>

