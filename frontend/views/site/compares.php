<?php
/**
 * @var $context \frontend\controllers\SiteController
 * @var $this \yii\web\View
 * @var $items backend\modules\catalog\models\Items[]
 */
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\OptionsCategory;
use yii\bootstrap\Html;

$context = $this->context;
$this->params['cats_compares'] = [];
$this->params['options_items'] = [];
$items_content = $this->render('//blocks/items_compares', ['items' => $items]);
if ($this->params['cats_compares']) {
    $cats = Category::find()->andWhere(['id' => $this->params['cats_compares']])->indexBy('id')->select(['name','id'])->column();
    /** @var OptionsCategory[] $options_category */
    $options_category = OptionsCategory::find()->andWhere(['cid' => $this->params['cats_compares'], 'isCompare' => 1])->with('option')->orderBy(['sort' => SORT_ASC])->all();
} else {
    $cats = $options_category = [];
}
?>
<section class="order-page__outer">
	<div class="__inner">
        <?= $this->render('//blocks/breadcrumbs') ?>

        <? if ($items): ?>
			<div class="scroll__table__wrapper">
				<table class="compare-table">
					<tbody>
					<tr>
						<td>
							<div class="string">
								<input id="options_view_all" type="radio" name="options_view" checked value="all">
								<label for="options_view_all">Все параметры</label>
							</div>
							<div class="string">
								<input id="options_view_diff" type="radio" name="options_view" value="diff">
								<label for="options_view_diff">Различающиеся</label>
							</div>
							<div class="string">
                                <?= Html::dropDownList('select_cat', null, $cats,
                                    [
                                        'class' => 'custom_select',
										'id'=>'change_view_cats'
                                    ]
                                ) ?>
							</div>
						</td>
                        <?= $items_content ?>
					</tr>
                    <? foreach ($options_category as $option_category): ?>
                        <?
                        $option = $option_category->option;
                        ?>
						<tr class="compare_options" data-cat="<?=$option_category->cid?>">
							<td><?= $option->name ?></td>
                            <? foreach ($items as $item): ?>
                                <? if (isset($this->params['options_items'][$item->id][$option->id])): ?>
                                    <?
                                    $strings_filters = $this->params['options_items'][$item->id];
                                    $strings_filter = $this->params['options_items'][$item->id][$option->id];
                                    $string = '';
                                    if ($option->measure) {
                                        if ($option->measure_position == 'right') {
                                            $string .= trim(implode(', ', $strings_filter['values']), ', ') . ' ' . $option->measure;
                                        } else {
                                            $string .= $option->measure . ' ' . trim(implode(', ', $strings_filter['values']), ', ');
                                        }
                                    } else {
                                        $string .= trim(implode(', ', $strings_filter['values']), ', ');
                                    }
                                    ?>
									<td class="item_option" data-item-id="<?=$item->id?>"><?= $string ?></td>
                                <? else: ?>
									<td class="item_option" data-item-id="<?=$item->id?>">-</td>
                                <? endif; ?>
                            <? endforeach; ?>
						</tr>
                    <? endforeach; ?>
					</tbody>
				</table>
			</div>
        <? else: ?>
			<div class="Text">
				Нет товаров для сравнения
			</div>
        <? endif; ?>
	</div>
</section>
<?
$this->registerJs('config_projects.page=\'compares\';'
    , $this::POS_BEGIN
);
?>