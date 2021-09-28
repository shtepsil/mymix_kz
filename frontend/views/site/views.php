<?php
/**
 * @var $context \frontend\controllers\SiteController
 * @var $this \yii\web\View
 * @var $items backend\modules\catalog\models\Items[]
 * @var $catalog_views array
 */
use backend\modules\catalog\models\Category;
use yii\helpers\Url;

$context = $this->context;
$cats = Category::getDb()->cache(
    function ($db) {
        return Category::find()->andWhere(['type' => 'items'])->indexBy('id')->all();
    },
    86400,
    new \yii\caching\TagDependency(['tags' => 'db_cache_catalog'])
);

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
                <div class="breadcrumbs__wrapper">
                    <?= $this->render('//blocks/breadcrumbs') ?>
                </div>
                <div class="catalog__container">
                    <div class="goods-line__array">
                        <? foreach ($catalog_views as $key=>$value): ?>
                            <?
                            if(!isset($items[$key])){
                                continue;
                            }
                            echo $this->render('//blocks/items_line', ['items' => [$items[$key]]]);
                            ?>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
