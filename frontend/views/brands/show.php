<?php
/**
 * @var  yii\web\View $this
 * @var \frontend\controllers\SiteController $context
 * @var Category[] $cats
 * @var Category|null $cat
 * @var Brands $brand
 * @var string $order
 */

use backend\modules\catalog\models\Brands;
use backend\modules\catalog\models\Category;
use yii\helpers\Html;

$context = $this->context;
$urlBrand = $brand->url();
?>
<div class="Goods goodslist padSpace">
    <h1 class="title"><?= $brand->name ?></h1>
    <div class="Filter line">
        <ul class="sort">
            <!--<li>Сортировать по</li>-->
            <?

            $sort_li = '';
            if ($order == 'price_asc') {
                $sort_li .= Html::tag(
                    'li',
                    Html::a(
                        'По алфавиту',
                        $brand->url(['order' => 'name_asc'])
                    )
                );
                $sort_li .= Html::tag(
                    'li',
                    'По цене',
                    [
                        'class' => 'byName',
                    ]
                );
            } elseif ($order == 'name_asc') {
                $sort_li .= Html::tag(
                    'li',
                    'По алфавиту',
                    [
                        'class' => 'byName',
                    ]
                );
                $sort_li .= Html::tag(
                    'li',
                    Html::a(
                        'По цене',
                        $brand->url(['order' => 'price_asc'])
                    )
                );
            }
            echo $sort_li
            ?>
        </ul>
		
		<?php
			$count_all_arrays =  count($data_cats_array, COUNT_RECURSIVE); 
			$count_all_height_arrays = count($data_cats_array); 
			$count_all = $count_all_arrays - $count_all_height_arrays;
		?>
		
        <? if ($cats): ?>
            <ul class="list">
                <?php

                $li_cats = '';
                $options = [
                    'all' => 'Все',
                ];
                $select_cat = false;
                // foreach ($cats as $sub) {
                    // if ($cat && $sub->id == $cat->id) {
                        // $content_li = $sub->name.' ('.$sub->countItems.')';
                        // $select_cat = $sub->parent;
                    // } else {
                        // $content_li = Html::a(
                            // $sub->name.' ('.$sub->countItems.')', $brand->url(['category_id' => $sub->id]),
                            // ['data' => ['category' => $sub->id]]
                        // );
                    // }
                    // $li_cats .= Html::tag('li', $content_li);
                    // $options[$sub->id] = $sub->name;
                // }
				 foreach ($data_cats as $sub) {

					if (!empty($data_cats_array[$sub->id])) {
						if ($cat && $sub->id == $cat->id) {

							$content_li = $sub->name.'('.count($data_cats_array[$sub->id]).')';
							$select_cat = $sub->parent;
							
						} else {										
							$content_li = Html::a(
								$sub->name.'('.count($data_cats_array[$sub->id]).')', $brand->url(['category_id' => $sub->id]),
								['data' => ['category' => $sub->id]]
							);
						}
						$li_cats .= Html::tag('li', $content_li);
						$options[$sub->id] = $sub->name;				
					}
                }
                ?>
                <?php if ($select_cat): ?>
                    <li><?= Html::a("Все($count_all)", $urlBrand, ['data' => ['category' => 'all']]) ?></li>
                <?php else: ?>
                    <li>Все(<?=$count_all?>)</li>
                <?php endif; ?>
                <?= $li_cats ?>
            </ul>
            <?php //echo Html::dropDownList(
               // 'category', $select_cat ? $cat->id : 'all', $options, ['data' => ['action' => 'change_category']]
         //   ) ?>
        <? endif ?>
    </div>
    <!--    <div class="goodsBlocks" data-check="height">-->
    <div class="goodsBlocks">
        <?= $this->render('//blocks/items', [
            'items' => $items,
            'md' => $md,
        ]) ?>
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
