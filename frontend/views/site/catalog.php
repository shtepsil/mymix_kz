<?php
/**
 * @var $this    yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $cat     Category
 * @var $cats    Category[]
 * @var $this      yii\web\View
 * @var $context   \frontend\controllers\SiteController
 * @var $items     array
 * @var $order     string
 * @var $measure   string
 * @var $items_cat Items[]
 * @var $model     Items
 * @var $cat       Category
 * @var $cats      Category[]
 * @var $sub_cats  Category[]
 * @var $sub_cat   Category
 * @var array $sel_filter
 * @var array $url_params
 * @var array $params_request
 * @var array $filters
 */

use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Items;
use yii\helpers\Html;

$context = $this->context;
$order_data = [
    'price_asc' => 'Цене',
    'new' => 'Новинкам',
    'popularity' => 'Популярности',
];
?>
<div class="breadcrumbsWrapper">
	<?= $this->render('//blocks/breadcrumbs') ?>
</div>
<div class="Goods goodslist padSpace">
    <h1 class="title"><?= ($cat->title) ? $cat->title : $cat->name ?></h1>
    <!--    <div class="goodsBlocks" data-check="height">-->
    <div class="catalog-filter__control">
        <div class="cf__filter__switch open">
            <?php if (count($filters) || count($cats) > 1): ?>
                <div class="__text"><i class="__show">Спрятать</i><i class="__hide">Показать</i>Фильтр</div>
            <?php endif ?>

        </div>
        <div class="cf__filter__sort">
            <div class="__text">Сортировать по
                <div class="active">
                    <span data-open="filter-sort" class=""><?= mb_strtolower($order_data[$order], 'UTF-8') ?></span>
                    <div data-open-wait="filter-sort" class="__subwindow"><i class="__triangle"></i>
                        <ul>

                            <?

                            $order_url = $url_params;
                            if ($params_request) {
                                $order_url['filter'] = Items::parseEncode($params_request);
                            }
                            foreach ($order_data as $key => $order_label) {
                                $options = [];
                                $url = $order_url;
                                if ($key == $order) {
                                    $options['class'] = 'current';
                                    if (isset($url['order'])) {
                                        unset($url['order']);
                                    }
                                } else {
                                    $url['order'] = $key;
                                }
                                echo Html::tag('li', Html::a($order_label, $url), $options);
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
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
                    <?php if ($cats && (count($cats) > 1 || (count($cats) == 1 && current($cats)->id != $cat->id))): ?>
                        <?php

                        $select_cat = false;

                        $filterCats = '';
                        foreach ($cats as $sub) {
                            if ($sub->id == $cat->id) {
                                $select_cat = $sub->parent;
                            }
                            $filterCats .= Html::tag(
                                'div',
                                Html::a(
                                    $sub->name, $sub->url(),
                                    [
                                        'data' => ['category' => $sub->id],
                                        'style' => ($sub->id == $cat->id ? '' : 'color: #8BC34A;'),
                                    ]
                                )
                                , ['class' => 'string']
                            );

                        }

                        ?>
                        <div class="cf__filter__column">
                            <div class="__title">Категории</div>
							<?PHP 
						//	echo 'sss';
							//var_dump($select_cat)?>
                            <?php if ($select_cat): ?>
                                <div class="string"><?= Html::a(
										$cat->parent->name, [$select_cat->url(), 'filter' => isset($_GET['filter']) ? $_GET['filter'] : null],
                                        [
                                            'data' => ['category' => 'all'],
                                            'style' => 'color: #8BC34A;',
                                        ]
                                    ) ?></div>
                            <?php else: ?>
                                <div class="string"><?= Html::a(
                                        $cat->name, '#',
                                        [
                                            'data' => ['category' => 'all'],
                                        ]
                                    ) ?></div>
                            <?php endif; ?>
							<?PHP //var_dump($cats)?>
                            <? foreach ($cats as $sub): ?>
                                <div style="margin-left:20px;display:inline;overflow:hidden" class="string">
                                    <?= Html::a(
                                        $sub->name, [$sub->url(), 'filter' => isset($_GET['filter']) ? $_GET['filter'] : null],
                                        [
                                            'data' => ['category' => $sub->id],
                                            'style' => ($sub->id == $cat->id ? '' : 'color: #8BC34A;'),
                                        ]
                                    ) ?>
                                </div>
                            <? endforeach; ?>
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
                    <?php endif ?>
                    <?php

                    foreach ($filters as $id_filter => $filter): ?>
                        <? if ($filter['type'] == 'multi_select' || $filter['type'] == 'one_select'): ?>
                            <div class="cf__filter__column">
                                <div class="__title"><?= $filter['name'] ?></div>
                                <?php foreach ($filter['values'] as $id_value => $value): ?>
                                    <?

                                    $checked = false;
                                    if (isset($sel_filter[$id_filter]) && in_array(
                                            $id_value, $sel_filter[$id_filter]
                                        )) {
                                        $checked = true;
                                    }
                                    ?>
                                    <div class="string">
                                        <input id="filter_<?= $id_value ?>"
                                               name="filters[<?= $filter['option_id'] ?>]"
                                               data-field="filters"
                                               data-field-type="checkbox"
                                               data-id_option="<?= $filter['option_id'] ?>"
                                               value="<?= $id_value ?>"
                                               type="checkbox" <?= $checked ? 'checked' : '' ?>>
                                        <label for="filter_<?= $id_value ?>"><?= $value ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <? endif; ?>

                    <?php endforeach; ?>
                </form>
            </div>
        </div>
        <div class="catalog-goods__column">
            <div class="goodsBlocks">
                <?= $this->render('//blocks/items', [
                    'items' => $items,
                    'md'=>$md,
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
