<?php
/**
 * @var $context \frontend\controllers\SiteController
 * @var $this \yii\web\View
 * @var $items backend\modules\catalog\models\Items[]
 * @var $pagination
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
                        <? foreach ($items as $item): ?>
                            <?
                            $is_bookmarks = false;
                            if (isset($this->params['bookmarks']) && isset($this->params['bookmarks'][$item->id])) {
                                $is_bookmarks = true;
                            }
                            ?>
							<div class="goods-line__block">
								<div class="goods-line__image">
									<a class="__image" href="<?= $item->url() ?>" style="background-image: url(<?= $item->img(true, 'mini') ?>)"></a>
								</div>
								<div class="goods-line__description">
									<div class="__category"><?= (isset($cats[$item->cid]) ? $cats[$item->cid]->name : $item->c->name) ?></div>
									<a class="__name" href="<?= $item->url() ?>"><?= $item->name ?></a>
								</div>
								<div class="goods-line__control-panel">
									<div class="__goods__delete" data-action="del_bookmarks" data-id="<?=$item->id?>"></div>
									<div class="__price">
                                        <? $item->real_price() ?>
                                        <? if ($item->price): ?>
                                            <? if ($item->old_price): ?>
												<div class="__old"><?= Yii::$app->formatter->asDecimal($item->old_price, 0) ?>
													<i class="tenge">b</i>
												</div>
                                            <? endif ?>
											<div class="__new"><?= Yii::$app->formatter->asDecimal($item->price) ?>
												<i class="tenge">b</i>
											</div>
                                        <? else: ?>
											<div class="__new">Цена по запросу</div>
                                        <? endif; ?>
									</div>
									<div class="__bottom__wrapper">
										<div class="__line">
                                            <? if($item->price): ?>
												<div class="counter">
													<div class="__minus" data-action="minus"></div>
													<div class="__num">
														<span data-selector="#item_list_<?=$item->id?>">1</span>
													</div>
													<div class="__plus" data-action="plus"></div>
												</div>
												<a class="btn__in-cart" href="<?= Url::to(['site/basket']) ?>" id="item_list_<?=$item->id?>" data-action="add_cart" data-id="<?=$item->id?>" data-count="1" >
                                                    <? if(isset($context->cart_items[$item->id])): ?>
														<span>В корзине</span>
                                                    <? else: ?>
														<span>В корзину</span>
                                                    <? endif; ?>
												</a>
                                            <? else: ?>
												<a class="btn__in-cart" href="basket.html">
													<span>Уточнить цену</span>
												</a>
                                            <? endif; ?>
										</div>
										<div class="__line">
											<a class="link__in-compare" href="#">
												<span>К сравнению</span>
											</a>
											<a class="link__in-bookmarks <?=$is_bookmarks?'active':''?>" href="<?= Url::to(['site/bookmarks']) ?>"
											   data-action="add_bookmarks"
											   data-id="<?=$item->id?>"
											>
												<span>В закладки</span>
											</a>
										</div>
									</div>
								</div>
							</div>
                        <? endforeach; ?>
					</div>
                    <?= $pagination ?>
				</div>
			</div>
		</div>
	</div>
</section>
