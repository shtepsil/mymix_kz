<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $item \backend\modules\catalog\models\Brands
 */
$context = $this->context;

?>
<section class="catalog-page__outer">
    <div class="__inner">
        <div class="catalog__columns">
            <div class="catalog__level__menu">
                <div class="__title">Каталог товаров</div>
                <?= $this->render('//blocks/category_left') ?>
                <?= $this->render('//blocks/banner_left') ?>
            </div>
            <div class="catalog__content">
                <section class="page__outer">
                    <div class="__inner">
                        <?= $this->render('//blocks/breadcrumbs') ?>
                        <div class="Text">
                           <?=$item->body?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
