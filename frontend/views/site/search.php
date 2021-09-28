<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $news \common\models\News[]
 * @var $items \common\models\Items[]
 */

use yii\helpers\Url;

use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Items;
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<div class="Goods goodslist padSpace sPage">
    <h1 class="title">Результаты поиска</h1>
    <form action="<?= Url::to(['site/search']) ?>" id="form__header__search_site" method="get" class="SearchPage">
        <input type="text" value="<?= $query_ ?>" name="query">
        <button class="custom" type="submit"></button>	
    </form>
    <br><br><br>	
	  <div class="catalog-filter__control">
        <div class="cf__filter__switch open">
                <div class="__text"><i class="__show">Спрятать</i><i class="__hide">Показать</i>Фильтр</div>
        </div>
    </div>	
	    <div class="catalog__column">
        <div class="catalog-filter__column">
            <div class="filter-block__header">
                <div class="filter-block__header_title">Фильтр</div>
                <div class="filter-block__header_close"></div>
            </div>
            <div id="filter__scrollbar" class="filter-block__body__wrapper filter_interface">
                <form action="" id="filters_form">                
					<div class="cf__filter__column">
						<div class="__title">Категории</div>
						<?php foreach ($cats_array_ as $key_ => $cat_array): ?>
							<div class="string"><?php /*echo Html::a(
									$cat_array['name'], [$cat_array['url'], 'filter' => isset($_GET['filter']) ? $_GET['filter'] : null],
									[
										'data' => ['category' => 'all'],
										'style' => '',
									]
								); 	*/ 
								echo '<p style="color: #8BC34A;font-size: 1.6em">' . $cat_array['name'] . '</p>'; 
								
								?></div>
					<ul class="__filter__list">
						<? 
						foreach ($cat_array['data_'][$key_] as $key => $result): ?>
								<li class="bran">
										<input class="restart" id="categories_<?= $key ?>"
											   data-field="categories"
											   data-field-type="checkbox"
											   data-id_option="<?= $key ?>"
											   name="categories[]"
											   value="<?= $key ?>"
											   type="checkbox" <?= isset($sel_categories[$key]) ? 'checked' : '' ?>>
										<label for="categories_<?= $key ?>"><?= $result ?></label>
									</li>
						<? endforeach; ?>
						</ul>
						<br><br>
					  <?php endforeach; ?>
					</div>						
					<? if ($all_statuses): ?>
						<div class="cf__filter__column">
							 <div class="__title">Статус</div>
							<div class="scroll_filter catalog__filter__line">       
								<ul class="__filter__list">
									<? foreach ($all_statuses as $key => $all_status): ?>
										<li class="bran">
											<input class="restart" id="status_<?= $key ?>"
												   data-field="statuses"
												   data-field-type="checkbox"
												   data-id_option="<?= $key ?>"
												   name="statuses[]"
												   value="<?= $key ?>"
												   type="checkbox" <?= isset($sel_status[$key]) ? 'checked' : '' ?>>
											<label for="status_<?= $key ?>"><?= $all_status ?></label>
										</li>
									<? endforeach; ?>
								</ul>
							</div>
						 </div>
					<? endif ?>						
                </form>
            </div>
        </div>
        <div class="catalog-goods__column">
            <div class="goodsBlocks">
                <?= $this->render('//blocks/items', [
                    'items' => $items,
                    'md' => $md,
                ]) ?>
            </div>	
            <?php if (false): ?>
                <!--            TODO добавить если будет много товаров-->
                <div class="btn__green" id="load_page" data-href="">Показать еще (21)</div>
            <?php endif ?>
        </div>
    </div>
</div>
<?

$this->registerJs(
    <<<JS
config_projects.page='catalog';
JS
    , $this::POS_BEGIN
);
?>
<?
$this->registerJs(
    <<<JS
$('[data-action="change_category"]').on('change',function(e) {
  var val = $(this).val();
  console.log($('[data-category="'+val+'"]'));
  window.location=$('[data-category="'+val+'"]').attr('href');
  
})
JS
    , $this::POS_END
);
?>