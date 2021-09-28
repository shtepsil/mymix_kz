<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $items \backend\modules\catalog\models\Brands[]
 */
$context = $this->context;

?>
<section class="catalog-page__outer">
    <div class="__inner">
        <div class="catalog__columns">
            <section class="brands__outer">
                <div class="__inner">
                    <?= $this->render('//blocks/breadcrumbs') ?>
                    <div class="brands__array">
                        <? foreach($items as $item): ?>
                            <a class="brands__block" href="<?= $item->url()?>">
                                <? if ($item->img): ?>
									<img src="<?= $item->img ?>">
                                <? endif ?>
                                <div class="__title"><span><?=$item->name?><?=($item->country?(' ('.$item->country.')'):'')?></span></div>
                            </a>
                        <? endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>
